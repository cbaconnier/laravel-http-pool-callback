<?php

namespace Cbaconnier\HttpPool\Facades;

use Cbaconnier\HttpPool\HttpPoolAware;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cbaconnier\HttpPool\HttpPool runAsync(HttpPoolAware[] $requests)
 * @method static \Cbaconnier\HttpPool\HttpPool run()
 * @method static \Cbaconnier\HttpPool\HttpPool requests(HttpPoolAware[] $requests)
 * @method static mixed[] getResolved()
 * @method static Response[] getResponses()
 */
class HttpPool extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cbaconnier\HttpPool\HttpPool::class;
    }
}
