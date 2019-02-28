# Phonet Api integration

You can receive original docs on [phonet](https://phonet.com.ua/) site

## Installation

```bash
composer require wearesho-team/phonet
```

## Configuration

Exist two implementation of configurations:
- [Config](./src/Config.php) - custom config
- [EnvironmentConfig](./src/EnvironmentConfig.php) - based on
[Horatius\Environment\Config](https://github.com/Horat1us/environment-config)

Available environment variables:

|variable|required|description|
|--------|--------|-----------|
|PHONET_DOMAIN|Yes|Domain name of your cms\system|
|PHONET_API_KEY|Yes|Special api key that ypu can receive in your Phonet account|

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

#### makeCall(string $operatorInternalNumber, string $targetNumber): string

Start new call and return unique uuid of it.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$service = new Phonet\Service($sender);

$uuid = $service->makeCall(
    $operatorInternalNumber = '001', // Internal number of operator
    $callTakerNumber = '380000000002' // Phone number of target
);
```

#### hangupCall(string $uuid): void

End a call / conversation by unique uuid

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$service = new Phonet\Service($sender);

$service->hangupCall(
    $uuid = 'uuid'
);
```

### Repository

Repository contains methods for searching data in Phonet Service.

#### activeCalls()

|param      |value            |
|-----------|-----------------|
|Return type| [Call\Active\Collection](src/Call/Active/Collection.php)|
|Arguments| - |

Returns collection of calls that currently taking place.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$activeCalls = $repository->activeCalls();
```

#### missedCalls(...)

|param      |value            |
|-----------|-----------------|
|Return type| [Call\Complete\Collection](src/Call/Complete/Collection.php)|
|Arguments|$from, $to, $directions, $limit, $offset|

Returns a collection of calls to call back.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$missedCalls = $repository->missedCalls(
    $from = new DateTime(),
    $to = new DateTime(),
    $directions = new Phonet\Call\Direction\Collection([/** @see Phonet\Call\Direction */]),
    $limit = 10, // count of needs calls
    $offset = 5 // shift in sample
);
```

#### companyCalls(...)

|param      |value            |
|-----------|-----------------|
|Return type| [Call\Complete\Collection](src/Call/Complete/Collection.php)|
|Arguments|$from, $to, $directions, $limit, $offset|

Returns a collection of calls made by the company.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$companyCalls = $repository->companyCalls(
    $from = new DateTime(),
    $to = new DateTime(),
    $directions = new Phonet\Call\Direction\Collection([/** @see Phonet\Call\Direction */]),
    $limit = 10, // count of needs calls
    $offset = 5 // shift in sample
);
```

#### usersCalls()

|param      |value            |
|-----------|-----------------|
|Return type| [Call\Complete\Collection](src/Call/Complete/Collection.php)|
|Arguments|$from, $to, $directions, $limit, $offset|

Returns a collection of calls made by employees.

```php
<?php

use Wearesho\Phonet;

/** @var Phonet\Sender $sender */

$repository = new Phonet\Repository($sender);

$usersCalls = $repository->usersCalls(
    $from = new DateTime(),
    $to = new DateTime(),
    $directions = new Phonet\Call\Direction\Collection([/** @see Phonet\Call\Direction */]),
    $limit = 10, // count of needs calls
    $offset = 5 // shift in sample
);
```

#### users()

|param      |value            |
|-----------|-----------------|
|Return type| [Employee\Collection](src/Employee/Collection.php)|
|Arguments| - |

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
