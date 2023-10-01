<?php

namespace Cbaconnier\HttpPool;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpPoolServiceProvider extends ServiceProvider
{

    public function register()
    {
        Http::macro('runAsync', function (array $requests) {
            return app(HttpPool::class)->runAsync($requests);
        });
    }

}
