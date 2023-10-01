<?php

namespace Cbaconnier\HttpPool;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HttpPool
{
    /** @var HttpPoolAware[] */
    protected array $pool = [];

    /** @var Response[] */
    protected array $responses = [];

    /** @param  HttpPoolAware[]  $requests */
    public function requests(array $requests): self
    {
        $this->pool = $requests;

        return $this;
    }

    public function run(): self
    {
        $repositories = collect($this->pool);

        $this->responses = Http::pool(fn (Pool $pool) => $repositories->map(
            fn (HttpPoolAware $repository, $key) => $repository
                ->setPendingRequest($pool->as($key))
                ->getPromise()
                ->call($this, $repository))
        );

        return $this;
    }

    /** @param  HttpPoolAware[]  $requests */
    public function runAsync(array $requests): self
    {
        $this->pool = $requests;

        return $this->run();
    }

    public function getResolved(): array
    {
        return collect($this->getResponses())
            ->map(fn (Response $response, $key) => $this->pool[$key]->handlePromiseResolved($response))
            ->toArray();
    }

    public function getResponses(): array
    {
        return $this->responses;
    }
}
