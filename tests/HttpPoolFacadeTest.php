<?php

namespace Cbaconnier\HttpPool\Tests;

use Cbaconnier\HttpPool\Facades\HttpPool;
use Cbaconnier\HttpPool\Tests\TestCallbacks\CustomTestRepository;
use Illuminate\Support\Facades\Http;

class HttpPoolFacadeTest extends TestCase
{
    protected CustomTestRepository $customTestRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->customTestRepository = new CustomTestRepository();
    }

    /** @test */
    public function it_can_run_through_a_facade(): void
    {
        Http::fake([
            'https://example.com' => Http::response('default'),
        ]);

        $results = HttpPool::runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
        ])->getResolved();

        $this->assertSame('test1', $results[0]);
    }

}
