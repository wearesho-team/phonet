# Phonet Api integration

## Installation

```bash
composer require wearesho-team/phonet
```

## Configuration

Exist two implementation of configurations:
- [Config](./src/Config.php) - custom config
- [EnvironmentConfig](./src/EnvironmentConfig.php) - based on
[Horatius\Environment\Config](https://github.com/Horat1us/environment-config)

Use [ConfigInterface](./src/ConfigInterface.php) to create your custom config

## Usage

### Provider

To use **Phonet** api you must authorize to service. For this exist [Authorization\ProviderInterface](./src/Authorization/ProviderInterface.php)

```php
<?php

use Wearesho\Phonet;

$client = new \GuzzleHttp\Client();
$provider = new Phonet\Authorization\Provider($client);
```

If You want cache your auth data use [CacheProvider](./src/Authorization/CacheProvider.php);

### Sender

All api methods used [Sender](./src/Sender.php)

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Authorization\ProviderInterface $provider */

$client = new \GuzzleHttp\Client();
$config = new Phonet\EnvironmentConfig();

$sender = new Phonet\Sender(
    $client,
    $config,
    $provider
);

```

### Service

Service contains only one method: `makeCall(string $callerNumber, string $callTakerNumber): string`

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$service = new Phonet\Service($sender);

// Return UUID of created call
$uuid = $service->makeCall(
    $callerNumber = '+380000000001',
    $callTakerNumber = '+380000000002'
);
```

### Repository

Repository contains methods for searching data in Phonet Service.

#### activeCalls(): Data\Collection\ActiveCall

Returns collection of calls that currently taking place.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$activeCalls = $repository->activeCalls();
```

#### missedCalls($from, $to, $directions, $limit, $offset): Data\Collection\CompleteCall

Returns a collection of calls to call back.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$missedCalls = $repository->missedCalls(
    $from = new DateTime(),
    $to = new DateTime(),
    $directions = new Phonet\Data\Collection\Direction([/** @see Phonet\Enum\Direction */]),
    $limit = 10, // count of needs calls
    $offset = 5 // shift in sample
);
```

#### companyCalls($from, $to, $directions, $limit, $offset): Data\Collection\CompleteCall

Returns a collection of calls made by the company.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$companyCalls = $repository->companyCalls(
    $from = new DateTime(),
    $to = new DateTime(),
    $directions = new Phonet\Data\Collection\Direction([/** @see Phonet\Enum\Direction */]),
    $limit = 10, // count of needs calls
    $offset = 5 // shift in sample
);
```

#### usersCalls($from, $to, $directions, $limit, $offset): Data\Collection\CompleteCall

Returns a collection of calls made by employees.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$usersCalls = $repository->usersCalls(
    $from = new DateTime(),
    $to = new DateTime(),
    $directions = new Phonet\Data\Collection\Direction([/** @see Phonet\Enum\Direction */]),
    $limit = 10, // count of needs calls
    $offset = 5 // shift in sample
);
```

#### users(): Data\Collection\Employee

Returns a collection of employees of company.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$users = $repository->users();
```

## Authors
- [Roman Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
