<?php

namespace Cbaconnier\HttpPool\Tests\TestCallbacks;

use Cbaconnier\HttpPool\HasHttpPool;
use Cbaconnier\HttpPool\HttpPoolAware;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

class CustomTestRepository implements HttpPoolAware
{
    use HasHttpPool;

    public string $baseUrl = 'https://example.com';

    public function getAsyncWithCallback(mixed $expected): PromiseInterface
    {
        $this->onPromiseResolved(fn (Response $response) => $expected);

        return $this->http()->get($this->baseUrl);
    }

    public function getAsyncWithoutCallback(): PromiseInterface
    {
        return $this->http()->get($this->baseUrl);
    }

    public function get(): Response
    {
        return $this->http()->get($this->baseUrl);
    }

}
