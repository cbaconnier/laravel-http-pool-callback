<?php

namespace Cbaconnier\HttpPool\Tests;

use Cbaconnier\HttpPool\Tests\TestCallbacks\CustomTestRepository;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;

class HttpPoolMacroTest extends TestCase
{
    protected CustomTestRepository $customTestRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->customTestRepository = new CustomTestRepository();
    }

    #[Test]
    public function it_can_run_through_a_macro(): void
    {
        Http::fake([
            'https://example.com' => Http::response('default'),
        ]);

        // @phpstan-ignore staticMethod.notFound
        $results = Http::runAsync([
            $this->customTestRepository->async(fn (CustomTestRepository $repository) => $repository->getAsyncWithCallback('test1')),
        ])->getResolved();

        $this->assertSame('test1', $results[0]);
    }
}
