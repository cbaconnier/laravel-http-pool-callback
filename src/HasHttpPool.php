<?php

namespace Cbaconnier\HttpPool;

use Closure;
use InvalidArgumentException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

trait HasHttpPool
{
    protected Closure|null $onPromiseResolvedCallback = null;

    protected PendingRequest|null $pendingRequest = null;

    protected Closure|null $promise = null;

    /** @param  Closure(self):mixed  $promise */
    public function async(Closure $promise): self
    {
        $repository = clone $this;

        $repository->promise = $promise;

        return $repository;
    }

    public function getPromise(): Closure
    {
        if ($this->promise === null) {
            throw new InvalidArgumentException('Promise has not been set. Call async() first.');
        }

        return $this->promise;
    }

    public function isPooling(): bool
    {
        return $this->pendingRequest !== null;
    }

    public function setPendingRequest(PendingRequest $pendingRequest): self
    {
        $this->pendingRequest = $pendingRequest;

        return $this;
    }

    public function http(): PendingRequest|Factory
    {
        if ($this->isPooling()) {
            if ($this->pendingRequest === null) {
                throw new InvalidArgumentException('PendingRequest is null despite pooling being active.');
            }
            return $this->pendingRequest;
        }

        return app(Factory::class);
    }

    /** @param  Closure(Response):mixed  $callback */
    public function onPromiseResolved(Closure $callback): self
    {
        $this->onPromiseResolvedCallback = $callback;

        return $this;
    }

    public function handlePromiseResolved(Response $response): mixed
    {
        if ($this->onPromiseResolvedCallback === null) {
            return $response;
        }

        return $this->onPromiseResolvedCallback->call($this, $response);
    }
}
