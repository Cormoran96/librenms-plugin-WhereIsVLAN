<?php

namespace App\Plugins\WhereIsVLAN;

use App\Plugins\MenuEntryHook;

class Menu extends MenuEntryHook
{
    public function __construct()
    {
        $this->weight = 60;
    }

    public function handle(): string
    {
        return view('plugins.whereisvlan.menu')->render();
    }
}
