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

.env

```
PREPR_ENDPOINT={YOUR_API_ENDPOINT}
PREPR_TIMEOUT=30
PREPR_CONNECT_TIMEOUT=10
PREPR_CUSTOMER_ID=
```

You can customize defaults in:

```
config/prepr.php
```

## Query the API

Option with query file (create file in app/Queries with .graphql extension):

```
$response = Http::prepr([
    'query' => 'name-of-the-file',
    'variables' => [
        'id' => 123,
    ]
]);
```

Option without a query file:

```
$response = Http::prepr([
    'raw-query' => 'query here',
    'variables' => [
        'id' => 123,
    ]
]);
```

Option with headers

```
$response = Http::prepr([
    'query' => 'name-of-the-file',
    'variables' => [
        'id' => 123
    ],
    'headers' => [
        'Prepr-Customer-Id' => request()->get('customer_id',request()->session()->getId())
    ]
]);
```
