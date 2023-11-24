<?php

namespace App\Classes;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Factory;


class Educar extends \Spekulatius\PHPScraper\PHPScraper
{
    public $urljuana = 'https://www.educ.ar/buscador?q=%2A&presentacion=lista&resources_formats=7&page=';
    function procesarpagina($urlpage)
    {
        echo PHP_EOL . str_repeat('*', 10) . PHP_EOL;
        echo $urlpage;
    }
    function titulos_por_pagina($link)
    {
        echo 'Visitando ' . $link . PHP_EOL;
        echo "--------------------" . PHP_EOL;
        $this->go($link);
        $titulos = $this->filterTexts("//*[@class='list-view-text-title']");
        $aux = [];
        foreach ($titulos as $titulo) {

            $aux[]  = $titulo;
        }
        return $aux;
    }
    function object($link, $type)
    {
        $response = Http::get($link);

        if ($response->status() == 404) {
            return false;
        }
        $this->go($link);
        if (Str::contains($type, 'Libro')) {

            $iframes = $this->filter("//body//iframe");
            foreach ($iframes as $iframe) {
                $src = ($iframe->getAttribute('src'));
                if (Str::contains($src, 'get-attachment')) {
                    return $src;
                }
            }
            //dump($iframe->getAttribute('src'));
            return;
        }

        echo "This page contains " . count($this->links) . " links.\n\n";

        // Loop through the links
        foreach ($this->links as $link) {
            echo " - " . $link . "\n";
        }
    }
    function resources_by_page($link)
    {
        $response = Http::get($link);

        if ($response->status() == 404)
            return false;
        echo 'url: '.$link ;
        $this->go($link);

        $enlaces = $this->filter('//*[@id="pills-list"]/a');

        $aux = [];
        foreach ($enlaces as $enlace) {
            $aux2 = [];
            if (@$titulo = $enlace->getElementsByTagName('p')->item(0)->nodeValue) {
                $aux2['name'] = trim($titulo);
            }
            if (@$link = $enlace->getAttribute('href')) {
                $aux2['url'] = trim($link);
            }
            if (@$tipo = $enlace->getElementsByTagName('h6')->item(0)->nodeValue) {
                $aux2['type'] = $this->cleantext($tipo);
            }
            list($internal_id) = sscanf($link, "https://www.educ.ar/recursos/%d/");
            $aux2['internal_id']  = $internal_id;

            $aux[]  = $aux2;
        }
        return $aux;
    }
    function cleantext($t)
    {
        if (strstr($t, '|')) {
            $ts = explode('|', $t);
            $c = array_map('trim', $ts);
            return implode('; ', $c);
        }
        return trim(str_replace(['|', '  '], ' ', $t));
    }
    function urlspaginador($desde, $hasta)
    {
        foreach (range($desde, $hasta) as $numeropagina)
            $aux[] = $this->urljuana . $numeropagina;
        return $aux;
    }

    function page($link, $horizontal = false)
    {

        $response = Http::get($link);

        if ($response->status() == 404)
            return false;
        try {
            $page = $this->go($link);
            //code...
        } catch (\Throwable $th) {
            return false;
        }

        //dump($page->core->client->internalResponse);

        $resumen = $this->openGraph();
        if ($resumen and is_array($resumen) and isset($resumen['og:description'])) {
            $resumen = $resumen['og:description'];
        }
        try {
            //code...
            if (!$horizontal)
                $datos_ficha = $this->filter('//*[@class="ficha-container"]');
            else
                $datos_ficha = $this->filter('//*[@class="ficha-container-horizontal"]');
        } catch (\Throwable $th) {
            return false;
        }
        $aux = [];
        //dump($datos_ficha) ;
        try {
            $ficha  = $datos_ficha->html();
        } catch (\Throwable $th) {
            return false;
        }



        $crawler = new Crawler($ficha);

        foreach ($datos_ficha as $domElement) {
            $titulo = '';
            if ($domElement->hasChildNodes()) {
                foreach ($domElement->childNodes as $node) {

                    if ($node->nodeName == 'p') {
                        $titulo = $node->textContent;
                        $aux[$titulo] = [];
                    } elseif ($node->nodeName == 'a') {
                        $aux[$titulo][] = $this->cleantext($node->textContent);
                    }
                }
            }
        }
        foreach ($aux as $titulo => $valores) {
            if (count($valores) == 0) {
                $aux[$titulo] = null;
            }
            if (Str::startsWith($titulo, 'Publicado:')) {
                $fecha = Str::after($titulo, 'Publicado: ');
                unset($aux[$titulo]);
                $aux['Publicado'] = $fecha;  // convertir a fecha real (string ahora)
            }
            if (Str::startsWith($titulo, 'Última modificación: ')) {
                $fechau = Str::after($titulo, 'Última modificación: ');
                unset($aux[$titulo]);
                $aux['Última modificación'] = $fechau;  // convertir a fecha real (string ahora)
            }
            if (Str::startsWith($titulo, 'Licencia')) {
                unset($aux[$titulo]);
            }
            if (Str::startsWith($titulo, 'Creative')) {
                $aux['Licencia'] = $titulo;
                unset($aux[$titulo]);
            }
        }
        if ($resumen) $aux['resumen'] = $resumen;
        return $aux;
    }
}
