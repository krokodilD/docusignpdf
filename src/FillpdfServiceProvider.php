<?php

namespace Daniilkrok\Fillpdf;

use Illuminate\Support\ServiceProvider;

class FillpdfServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Daniilkrok\Fillpdf\FillpdfController');
    }
}
