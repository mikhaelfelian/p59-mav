<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Toolbar extends BaseConfig
{
    /**
     * Disable all collectors for faster local dev.
     */
    public array $collectors = [
        \CodeIgniter\Debug\Toolbar\Collectors\Timers::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Database::class,
        \CodeIgniter\Debug\Toolbar\Collectors\Logs::class,
    ];

    /**
     * Don’t collect view vars to save memory.
     */
    public bool $collectVarData = false;

    /**
     * No history stored.
     */
    public int $maxHistory = 0;

    /**
     * Toolbar view path (keep default, just in case).
     */
    public string $viewsPath = SYSTEMPATH . 'Debug/Toolbar/Views/';

    /**
     * Limit number of stored queries (safe cap).
     */
    public int $maxQueries = 20;

    /**
     * Disable toolbar completely in all environments.
     */
    public bool $enabled = false;
}
