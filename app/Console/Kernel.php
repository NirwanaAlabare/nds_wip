<?php

namespace App\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('production:fixrollqty')->hourlyAt(10)->between('8:00', '20:00')->sendOutputTo(public_path().'/tasks/log.txt');
        $schedule->command('production:missrework')->hourlyAt(10)->between('8:00', '20:00')->sendOutputTo(public_path().'/tasks/log.txt');
        $schedule->command('production:missreject')->hourlyAt(10)->between('8:00', '20:00')->sendOutputTo(public_path().'/tasks/log.txt');
        $schedule->command('general:updatemastersb')
            ->everyMinute()
            ->when(function () {
                $now = now();

                // Only between 8:00 AM and 8:00 PM
                if ($now->lt($now->copy()->setTime(8, 0)) ||
                    $now->gt($now->copy()->setTime(20, 0))) {
                    return false;
                }

                // Minutes since 8:00 AM
                $minutes = $now->diffInMinutes(
                    $now->copy()->setTime(8, 0)
                );

                return $minutes % 45 === 0;
            });
        $schedule->command('general:updatemgtreptmpearn')->dailyAt("01:00")->sendOutputTo(public_path().'/tasks/log.txt');
        $schedule->command('dc:rekap')->lastDayOfMonth('23:30')->sendOutputTo(public_path().'/tasks/log.txt');
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
