<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\Resource ;
class Database extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:database {--list-resources}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'data stats';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo Resource::count();
        $n=1;
        if ($this->option('list-resources')){
            foreach (Resource::all() as $resource) {
                echo
                $n++.','.
                $resource->internal_id. ','.
                '"'.
                $resource->name.'","'.$resource->url.'","'.$resource->type.'"'.PHP_EOL;
            }
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
