protected $commands = [
    \App\Console\Commands\BackupRun::class,
];

protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:run')->daily();
}