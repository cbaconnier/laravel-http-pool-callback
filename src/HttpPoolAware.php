<?php

namespace Cbaconnier\HttpPool;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

interface HttpPoolAware
{
    public function getPromise(): Closure;

    public function setPendingRequest(PendingRequest $pendingRequest): self;

    public function handlePromiseResolved(Response $response): mixed;
}
