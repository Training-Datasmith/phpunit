--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6279
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/6279';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.D.DD....DD.                                                      12 / 12 (100%)

Time: %s, Memory: %s

5 tests triggered 5 deprecations:

1) %sTriggersDeprecationInDataProvider1Test.php:29
some deprecation

Triggered by:

* PHPUnit\TestFixture\Issue6279\TriggersDeprecationInDataProvider1Test::method2#0
  %sTriggersDeprecationInDataProvider1Test.php:51

* PHPUnit\TestFixture\Issue6279\TriggersDeprecationInDataProvider1Test::method4#0
  %sTriggersDeprecationInDataProvider1Test.php:64

2) %sTriggersDeprecationInDataProvider1Test.php:36
first

3) %sTriggersDeprecationInDataProvider1Test.php:37
second

4) %sTriggersDeprecationInDataProviderUsingIgnoreDeprecationsTest.php:35
some deprecation 2

5) %sTriggersDeprecationInDataProviderUsingIgnoreDeprecationsTest.php:42
some deprecation 3

OK, but there were issues!
Tests: 12, Assertions: 12, Deprecations: 5.
