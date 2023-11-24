<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use \App\Classes\Educar;
use \App\Resource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Objects extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:object {id} {--only-view}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Obtención de objetos digitales de una pagina, se debe  pasar el id de recurso (internal_id) ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $idresource = $this->argument('id');
        if (!is_numeric($idresource)) {
            $res = Resource::where('type', 'like', $idresource . '%')->orderby('internal_id')->get();

            foreach ($res as $re) {
                $this->getremote($re->internal_id);
            }
        } else {
            $this->getremote($idresource);
        }
    }

    public function getremote($idresource)
    {
        $res = Resource::where('internal_id', $idresource)->first();

        if ($res and $res->url) {


            $url = $res->url;
            $web = new Educar();
            $filename = 'objetos/' . $res->internal_id . '/' . $res->internal_id . '.pdf';
            if (storage::exists($filename))
                return true;
            $this->info('Obtener ' . $res->internal_id . ' ' . $url);
            $src = $web->object($url, $res->type);

            if ($src) {
                $this->info('encontrados ' . $src);
                try {
                    $response = Http::connectTimeout(300)->get($src);
                    //code...
                } catch (\Throwable $th) {
                    return false;
                }

                if ($response->ok())
                    return Storage::put($filename, $response->body());
                //$this->info('Grabado '. $idresource) ;
            }
        } else $this->error('Error : ' . $idresource . ' no encontrado');
    }
}
