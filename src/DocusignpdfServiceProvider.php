<?php

namespace Daniilkrok\Docusignpdf;

use Illuminate\Support\ServiceProvider;

class DocusignpdfServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // loading the routes file
        include __DIR__.'/Http/routes.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //$this->app->make('Daniilkrok\Docusignpdf\DocusignpdfController');
        $this->app->bind('docusignpdf', function ($app){
           return new Docusignpdf;
        });
    }
}
