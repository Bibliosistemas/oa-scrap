<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use \App\Classes\Educar;
use \App\Resource;
class Page extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:page {id} {--only-view}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Obtención de páginas ̣{id} se debe  pasar el id de recurso (internal_id) ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $res = Resource::where('internal_id', $this->argument('id'))->first();

        if ($res and $res->url){
if($this->option('only-view')) {
    dump($res) ;
    exit ;
}
            $url = $res->url;
            $web = new Educar();
            $this->info('Obtener '.$res->internal_id.' '. $url)  ;
            $campos = $web->page($url,false) ;
            if (!$campos) {
                $this->info('intentamos horizontal');
            $campos = $web->page($url,true) ;

            }
            if ($campos  and is_Array($campos)) {
                $res->metadata  = json_encode($campos) ;
                $res->save();
                $this->info('Grabado '. $this->argument('id')) ;
            }else
            {
                $res->metadata  = json_encode(['error'=>'404']) ;
                $res->save();
                $this->error('Error 404 '. $this->argument('id')) ;

            }
        }
        else $this->error('Error : '.$this->argument('id') . ' no encontrado') ;
      }



}
