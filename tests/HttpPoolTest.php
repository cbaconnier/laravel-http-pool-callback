<?php

namespace Cbaconnier\HttpPool\Tests;

use Cbaconnier\HttpPool\HttpPool;
use Cbaconnier\HttpPool\Tests\TestCallbacks\CustomTestRepository;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HttpPoolTest extends TestCase
{
    protected HttpPool $httpPool;

    protected CustomTestRepository $customTestRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpPool = new HttpPool();
        $this->customTestRepository = new CustomTestRepository();
    }

    /** @test */
    public function it_executes_the_callback(): void
    {
        Http::fake();

        $results = $this->httpPool->runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
        ])->getResolved();

        $this->assertSame('test1', $results[0]);
    }

    /** @test */
    public function it_run_a_request_without_callback(): void
    {
        Http::fake([
                $this->customTestRepository->baseUrl => Http::response('default'),
            ]);

        $results = $this->httpPool->runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithoutCallback()),
        ])->getResolved();

        $this->assertInstanceOf(Response::class, $results[0]);
        $this->assertSame('default', $results[0]->body());
    }

    /** @test */
    public function it_executes_the_callback_without_conflicts(): void
    {
        Http::fake();

        $results = $this->httpPool->runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test2')),
        ])->getResolved();

        $this->assertSame('test1', $results[0]);
        $this->assertSame('test2', $results[1]);
    }

    /** @test */
    public function it_returns_the_responses(): void
    {
        Http::fake([
            $this->customTestRepository->baseUrl => Http::response('default'),
        ]);

        $results = $this->httpPool->runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
        ])->getResponses();

        $response = $results[0];

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('default', $response->body());
    }

    /** @test */
    public function repository_can_still_run_normal_requests(): void
    {
        Http::fake([
            $this->customTestRepository->baseUrl => Http::response('default'),
        ]);

        $response = $this->customTestRepository->get();

        $this->assertSame('default', $response->body());
    }

    /** @test */
    public function it_can_set_requests_then_run_the_pool(): void
    {
        Http::fake();

        $this->httpPool->requests([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
        ]);

        Http::assertNothingSent();

        $results = $this->httpPool->run();

        Http::assertSent(function ($request) {
            return $request->url() === $this->customTestRepository->baseUrl;
        });

        $this->assertSame('test1', $results->getResolved()[0]);

    }

    /** @test */
    public function it_can_execute_the_requests_keys(): void
    {
        Http::fake();

        $results = $this->httpPool->runAsync([
            '1.com' => $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
            '2.com' => $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test2')),
        ])->getResolved();


        $this->assertSame('test1', $results['1.com']);
        $this->assertSame('test2', $results['2.com']);
    }

    /** @test */
    public function it_create_async_requests(): void
    {
        Http::fake();

        $this->httpPool->runAsync([
            $promise = $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
        ])->getResolved();

        $this->assertTrue($promise->isPooling());

        $pendingRequest = (fn () => $this->pendingRequest)->call($promise);

        // The PendingRequest promise is only available when running async requests.
        $this->assertInstanceOf(PromiseInterface::class, $pendingRequest?->getPromise());
    }

    /** @test */
    public function it_preserve_the_data(): void
    {
        Http::fake();

        $expected = collect();

        $results = $this->httpPool->runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback($expected)),
        ])->getResolved();

        $this->assertSame($expected, $results[0]);
    }

}
