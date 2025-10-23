<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SyncAzureStorage::class,
        \App\Console\Commands\MigrateToAzure::class,
        \App\Console\Commands\FixAzurePaths::class,
        \App\Console\Commands\ImportLocalComics::class,
        \App\Console\Commands\ImportCovers::class,
    ];

    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        //
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
