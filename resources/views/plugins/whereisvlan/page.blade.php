@extends('layouts.librenmsv1')

@section('title', 'WhereIsVLAN')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>WhereIsVLAN - Find Ports by VLAN</h2>
            <p class="text-muted">Search for switch ports that have specific VLANs configured</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Search Parameters</h3>
                </div>
                <div class="panel-body">
                    <form action="{{ url('plugin/v1/WhereIsVLAN') }}" method="POST" class="form-horizontal">
                        @csrf

                        <div class="form-group">
                            <label for="vlan_to_search" class="col-sm-3 control-label">VLAN ID(s)</label>
                            <div class="col-sm-9">
                                <input type="text"
                                       class="form-control"
                                       id="vlan_to_search"
                                       name="vlan_to_search"
                                       value="{{ $vlan_to_search }}"
                                       placeholder="e.g., 100 or 100,200,300 or 100-110">
                                <p class="help-block">
                                    Enter single VLAN (e.g., <code>100</code>),
                                    multiple VLANs separated by commas (e.g., <code>100,200,300</code>),
                                    or a range (e.g., <code>100-110</code>)
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"
                                               name="exclude_untagged"
                                               value="1"
                                               {{ $exclude_untagged ? 'checked' : '' }}>
                                        Exclude access ports (untagged)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"
                                               name="exclude_tagged"
                                               value="1"
                                               {{ $exclude_tagged ? 'checked' : '' }}>
                                        Exclude trunk ports (tagged)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($errors))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <h4><i class="fa fa-exclamation-triangle"></i> Errors</h4>
                    <ul>
                        @foreach ($errors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($result))
        <div class="row">
            <div class="col-md-12">
                <h3>Results</h3>
                @foreach ($result as $device_id => $device_data)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a href="{{ url("device/device=$device_id/") }}">
                                    <i class="fa fa-server"></i> {{ $device_data['sysName'] }}
                                </a>
                                <span class="text-muted">({{ $device_data['hostname'] }})</span>
                            </h4>
                        </div>
                        <div class="panel-body">
                            @foreach ($device_data['vlan'] as $vlan => $vlan_data)
                                <div class="vlan-section">
                                    <h5 class="bg-info" style="padding: 10px; margin-top: 0;">
                                        VLAN {{ $vlan }}
                                        @if ($vlan_data['vlan_name'])
                                            <span class="text-muted">({{ $vlan_data['vlan_name'] }})</span>
                                        @endif
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-condensed table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Interface</th>
                                                    <th>Description</th>
                                                    <th>Type</th>
                                                    <th>Oper Status</th>
                                                    <th>Admin Status</th>
                                                    <th>Speed</th>
                                                    <th>Duplex</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($vlan_data['ports'] as $port_id => $port_data)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ url("device/device=$device_id/tab=port/port=$port_id/") }}">
                                                                <strong>{{ $port_data['ifName'] }}</strong>
                                                            </a>
                                                        </td>
                                                        <td>{{ $port_data['ifAlias'] ?: '-' }}</td>
                                                        <td>
                                                            @if ($port_data['untagged'])
                                                                <span class="label label-success">Access</span>
                                                            @else
                                                                <span class="label label-info">Trunk</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($port_data['ifOperStatus'] == 'up')
                                                                <span class="label label-success">{{ $port_data['ifOperStatus'] }}</span>
                                                            @elseif ($port_data['ifOperStatus'] == 'down')
                                                                <span class="label label-danger">{{ $port_data['ifOperStatus'] }}</span>
                                                            @else
                                                                <span class="label label-default">{{ $port_data['ifOperStatus'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($port_data['ifAdminStatus'] == 'up')
                                                                <span class="label label-success">{{ $port_data['ifAdminStatus'] }}</span>
                                                            @elseif ($port_data['ifAdminStatus'] == 'down')
                                                                <span class="label label-danger">{{ $port_data['ifAdminStatus'] }}</span>
                                                            @else
                                                                <span class="label label-default">{{ $port_data['ifAdminStatus'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($port_data['ifSpeed'])
                                                                {{ number_format($port_data['ifSpeed'] / 1000000, 0) }} Mbps
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ $port_data['ifDuplex'] ?: '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif (!empty($vlan_to_search) && empty($errors))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No ports found with the specified VLAN(s).
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .vlan-section {
        margin-bottom: 30px;
    }
    .vlan-section:last-child {
        margin-bottom: 0;
    }
</style>
@endsection
