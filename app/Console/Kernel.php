<?php

namespace App\Console;

use App\Console\Commands\Carrier\AddToBoxes;
use App\Console\Commands\Carrier\Depesh;
use App\Console\Commands\Carrier\RefreshCarrierData;
use App\Console\Commands\Carrier\ResendPackagesWithChangedClients;
use App\Console\Commands\Carrier\SendToCustoms;
use App\Console\Commands\Carrier\SendToCommercial;
use App\Console\Commands\GroupList;
use App\Console\Commands\PartnerCreateCourier;
use App\Console\Commands\ResendFailedMails;
use App\Console\Commands\SendToAzerpost;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ResendFailedMails::class,
        RefreshCarrierData::class,
        SendToCustoms::class,
        AddToBoxes::class,
        Depesh::class,
        ResendPackagesWithChangedClients::class,
        GroupList::class,
        SendToCommercial::class,
        PartnerCreateCourier::class,
        SendToAzerpost::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(RefreshCarrierData::class)->everyFiveMinutes(); //declaredDeleteDeclared
        $schedule->command(SendToCustoms::class)->everyFiveMinutes();
        $schedule->command(SendToCommercial::class)->hourly();
        //$schedule->command(ResendFailedMails::class)->everyMinute();
        $schedule->command(AddToBoxes::class)->everyMinute();
        $schedule->command(Depesh::class)->everyFiveMinutes();
        $schedule->command(ResendPackagesWithChangedClients::class)->everyMinute();
        $schedule->command(PartnerCreateCourier::class)->hourly();
        $schedule->command(SendToAzerpost::class)->hourly();
        //$schedule->command(SendToAzerpost::class)->cron('0 */4 * * *');
        //$schedule->command(GroupList::class)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
