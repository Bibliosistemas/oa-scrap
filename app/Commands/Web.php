<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use \App\Classes\Educar;
use \App\Resource;
use Illuminate\Support\Str;
class Web extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'resources {--truncate}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'captura todos los registros de educ.ar usando el paginador {--truncate} para borrar toda la base antes';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $web = new Educar();
        $pages = $web->urlspaginador(1,1000);
        if ($this->option('truncate')) {
            Resource::truncate();
            $this->info('Se ha borrado los registros de resources');
        }
        $this->call('app:database');

dump($pages) ;

        foreach ($pages as $link) {
            if (strstr($link, 'page=')) {
                $resources = $web->resources_by_page($link) ;

                if ($resources) {

                    foreach ($resources as $resource) {
                        if ($res2= Resource::updateOrCreate(['internal_id' => $resource['internal_id']], $resource)) {
                            echo PHP_EOL . 'creado o modificado: ' . $resource['internal_id'];
                            // if ($res2->metadata  ==null or Str::contains($res2->metadata , '404'))
                            // $this->call('app:page' , ['id'=>$resource['internal_id']]) ;
                            // else
                            $this->info('Ya fue capturado '.$res2->type) ;
                        } else
                            echo PHP_EOL . 'error : ' . $resource['internal_id'];
                    }
                }
            }
        }
        $this->info('Cantidad de registros');
        $this->call('app:database');
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
