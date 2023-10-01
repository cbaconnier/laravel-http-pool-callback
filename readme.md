# [wip] Laravel Http Pool - Callback

Laravel Http Pool - Callback is a package that enhances the HTTP pool functionality in Laravel. It allows users to apply callbacks to the results of HTTP requests made using Laravel's HTTP pool. This makes it easier to process and manipulate the responses as needed.

## Installation

You can install the package via Composer:

```bash
composer require cbaconnier/laravel-http-pool-callback
```

## Usage

### Prepare the requests

You must implement the `Cbaconnier\HttpPool\HttpPoolAware` interface and use the `Cbaconnier\HttpPool\HttpPoolAwareTrait` trait in your repository classes.  
This will allow you to register callbacks to be applied to the responses of the requests.

```php

use GuzzleHttp\Promise\Promise;
use Cbaconnier\HttpPool\HttpPoolAware;
use Cbaconnier\HttpPool\HttpPoolAware;

class InvoiceRepository implements HttpPoolAware
{

    use HttpPoolAware;

    public function findAsync(int $id): Promise
    {
        $promise = $this->http()->get("https://example.com/invoices/{$id}");

        // Register the callback that will be executed on the response
        $this->onPromiseResolved(function (Response $response) {
            return new InvoiceDto($response->getBody()->getContents());
        });

        return $promise;
    }

    public function find(int $id): InvoiceDto
    {
        // You can still use the repository without async requests as well
        $response = $this->http()->get("https://example.com/invoices/{$id}");

        return new InvoiceDto($response->getBody()->getContents());
    }

    // Not necessary but here to demonstrate a common use case
    // Then use $this->client()->get(...) instead of $this->http()->get(...)
    public function client(): PendingRequest
    {
        return $this->http()
        ->withHeaders([
            'Authorizations' => 'Bearer ...'
        ])
        ->withOptions([
            // ..
        ]);
    }

}

```

### Pool the requests and apply the callbacks
`runAsync` will clone the repositories instances to no infer with other requests and delegate the requests to the native Http::pool() method.  
By doing so, you can still benefit from the `Http` methods and testing helpers.

When you call `getResponses`, it will returns the default Guzzle `Response` objects.  
When you call `getResolved`, it will apply the `callbacks` to the responses and returns the results.

```php
use Cbaconnier\HttpPool\Facades\HttpPool;

$pool = HttpPool::runAsync([
        'invoice' => $invoiceRepository->async(fn (InvoiceRepository $repository) => $repository->findAsync(123)),
        'client' => $clientRepository->async(fn (ClientRepository $repository) => $repository->findAsync(123)),
    ]);

$pool->getResponses(); // Returns ['invoice' => Response, 'client' => Response]
$pool->getResolved();  // Returns ['invoice' => InvoiceDto, 'client' => ClientDto]

// You can also use macro instead of facade
Http::runAsync([ ... ])->getResolved();

```

## Testing

```bash
composer test
```

## Security

If you discover any security related issues, please email the author instead of using the issue tracker.

## Credits

- [Clément Baconnier](https://github.com/cbaconnier)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
