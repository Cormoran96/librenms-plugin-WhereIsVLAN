<?php

namespace App\Plugins\WhereIsVLAN;

use App\Plugins\PageHook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Page extends PageHook
{
    public function handle(Request $request): string
    {
        $result = [];
        $errors = [];
        $vlanToSearch = '';
        $excludeUntagged = false;
        $excludeTagged = false;

        if ($request->isMethod('post')) {
            $vlanToSearch = $request->input('vlan_to_search', '');
            $excludeUntagged = (bool) $request->input('exclude_untagged', false);
            $excludeTagged = (bool) $request->input('exclude_tagged', false);

            if ($vlanToSearch !== '') {
                try {
                    $vlans = $this->parseVlanInput($vlanToSearch);

                    if (! empty($vlans)) {
                        $result = $this->searchVlans($vlans, $excludeUntagged, $excludeTagged);
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return view('plugins.whereisvlan.page', [
            'result' => $result,
            'errors' => $errors,
            'vlan_to_search' => $vlanToSearch,
            'exclude_untagged' => $excludeUntagged,
            'exclude_tagged' => $excludeTagged,
        ])->render();
    }

    /**
     * Parse VLAN input string into array of VLAN IDs
     * Supports: single VLANs (100), comma-separated (100,200,300), and ranges (100-110)
     *
     * @param  string  $input
     * @return array
     *
     * @throws \Exception
     */
    private function parseVlanInput(string $input): array
    {
        $vlans = [];
        $parts = explode(',', $input);

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/^(\d+)-(\d+)$/', $part, $matches)) {
                // Handle range (e.g., "100-110")
                $start = (int) $matches[1];
                $end = (int) $matches[2];

                if ($start > 4094 || $end > 4094) {
                    throw new \Exception("VLAN ID must be between 1 and 4094. Invalid value in range: {$part}");
                }

                if ($start <= $end) {
                    for ($i = $start; $i <= $end; $i++) {
                        $vlans[] = $i;
                    }
                } else {
                    for ($i = $start; $i >= $end; $i--) {
                        $vlans[] = $i;
                    }
                }
            } elseif (is_numeric($part)) {
                // Handle single VLAN
                $vlan = (int) $part;
                if ($vlan < 1 || $vlan > 4094) {
                    throw new \Exception("VLAN ID must be between 1 and 4094. Invalid value: {$vlan}");
                }
                $vlans[] = $vlan;
            } elseif ($part !== '') {
                throw new \Exception("Invalid VLAN format: '{$part}'. Use numbers, comma-separated values, or ranges (e.g., 100-110)");
            }
        }

        return array_unique($vlans);
    }

    /**
     * Search for VLANs in the database
     *
     * @param  array  $vlans
     * @param  bool  $excludeUntagged
     * @param  bool  $excludeTagged
     * @return array
     */
    private function searchVlans(array $vlans, bool $excludeUntagged, bool $excludeTagged): array
    {
        $query = DB::table('ports_vlans')
            ->join('devices', 'ports_vlans.device_id', '=', 'devices.device_id')
            ->join('ports', 'ports_vlans.port_id', '=', 'ports.port_id')
            ->join('vlans', function ($join) {
                $join->on('vlans.device_id', '=', 'devices.device_id')
                    ->on('vlans.vlan_vlan', '=', 'ports_vlans.vlan');
            })
            ->whereIn('ports_vlans.vlan', $vlans)
            ->select(
                'ports_vlans.device_id',
                'ports_vlans.port_id',
                'ports_vlans.vlan',
                'ports_vlans.untagged',
                'vlans.vlan_name',
                'devices.sysName',
                'devices.hostname',
                'ports.ifName',
                'ports.ifAlias',
                'ports.ifSpeed',
                'ports.ifDuplex',
                'ports.ifOperStatus',
                'ports.ifAdminStatus'
            );

        if ($excludeUntagged) {
            $query->where('ports_vlans.untagged', '!=', 1);
        }

        if ($excludeTagged) {
            $query->where('ports_vlans.untagged', '!=', 0);
        }

        $query->orderBy('ports_vlans.device_id')
            ->orderBy('ports.ifName');

        $rows = $query->get();

        // Organize results by device and VLAN
        $result = [];
        foreach ($rows as $row) {
            $deviceId = $row->device_id;
            $vlan = $row->vlan;
            $portId = $row->port_id;

            if (! isset($result[$deviceId])) {
                $result[$deviceId] = [
                    'sysName' => $row->sysName,
                    'hostname' => $row->hostname,
                    'vlan' => [],
                ];
            }

            if (! isset($result[$deviceId]['vlan'][$vlan])) {
                $result[$deviceId]['vlan'][$vlan] = [
                    'vlan_name' => $row->vlan_name,
                    'ports' => [],
                ];
            }

            $result[$deviceId]['vlan'][$vlan]['ports'][$portId] = [
                'ifName' => $row->ifName,
                'ifAlias' => $row->ifAlias,
                'ifSpeed' => $row->ifSpeed,
                'ifDuplex' => $row->ifDuplex,
                'ifOperStatus' => $row->ifOperStatus,
                'ifAdminStatus' => $row->ifAdminStatus,
                'untagged' => $row->untagged,
            ];
        }

        return $result;
    }
}
