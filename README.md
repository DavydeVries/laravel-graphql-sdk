# Laravel GraphQL SDK
This Laravel package is a provider for the Prepr GraphQL API.

Architecture:
- `PreprServiceProvider` handles package bootstrapping (config merge, macros, commands, publishing).
- `PreprClient` handles request sending and payload/header building.

## How to install

Install Package

```
composer require preprio/laravel-graphql-sdk
```

Publish and install package config

```
php artisan prepr:install
```

This also creates:

```
app/Queries/graphql.config.yml
```

If `app/Queries/graphql.config.yml` already exists, install will skip it and keep your existing file unchanged.

.env

```
PREPR_ENDPOINT={YOUR_API_ENDPOINT}
PREPR_TIMEOUT=30
PREPR_CONNECT_TIMEOUT=10
```

You can customize defaults in:

```
config/prepr.php
```

## Query the API

Option with a query file (create a file in app/Queries with .graphql extension):

```php
$response = Http::prepr([
    'query' => 'name-of-the-file',
    'variables' => [
        'id' => 123,
    ]
]);
```

Option without a query file:

```php
$response = Http::prepr([
    'raw-query' => 'query here',
    'variables' => [
        'id' => 123,
    ]
]);
```

Option with headers

```php
$response = Http::prepr([
    'query' => 'name-of-the-file',
    'variables' => [
        'id' => 123,
    ],
    'headers' => [
        'Prepr-Customer-Id' => request()->get('customer_id', request()->session()->getId()),
    ],
]);
```

Per-request `headers` are merged with `config('prepr.headers')`; null and empty string values are filtered out.

## Using Http::pool

For more details about request pooling, see the Laravel docs: https://laravel.com/docs/12.x/http-client#request-pooling

Example A: run query files in parallel:

```php
$responses = Http::pool(fn ($pool) => [
    $pool->prepr([
        'query' => 'homepage',
        'variables' => ['locale' => 'en-US'],
    ]),
    $pool->prepr([
        'query' => 'navigation',
        'variables' => ['locale' => 'en-US'],
    ]),
]);

$homepage = $responses[0]->json();
$navigation = $responses[1]->json();
```

Example B: mix query file and raw query in one pool:

```php
$responses = Http::pool(fn ($pool) => [
    $pool->prepr([
        'query' => 'article-by-slug',
        'variables' => ['slug' => 'hello-world'],
    ]),
    $pool->prepr([
        'raw-query' => 'query Settings { Settings { title } }',
    ]),
]);

if ($responses[0]->successful() && $responses[1]->successful()) {
    $article = $responses[0]->json();
    $settings = $responses[1]->json();
}
```

## Preview / Visual Editing Header Overrides

When preview inputs are present on the current request, the SDK automatically adjusts Prepr headers:

- `prepr_preview_segment` sets `Prepr-Segments`
- `prepr_preview_ab` sets `Prepr-ABtesting`
- If either preview input is present, `Prepr-Customer-Id` is removed for that request

Practical example:

```php
$response = Http::prepr([
    'query' => 'homepage',
    'headers' => [
        'Prepr-Customer-Id' => request()->session()->getId(),
    ],
]);
```

If the current request contains `prepr_preview_segment` and/or `prepr_preview_ab`, those preview headers are applied and the customer header is unset automatically.

Header precedence:

1. Start from `config('prepr.headers')`
2. Merge runtime `headers` from `Http::prepr([...])`
3. Apply preview overrides from request input
4. Remove null and empty header values
