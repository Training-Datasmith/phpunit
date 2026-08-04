--TEST--
E_DEPRECATED classified correctly by source (test, first-party, third-party)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/php-deprecation';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (%sphpunit.xml, 3 tests)
Test Suite Started (default, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromTestCode)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromTestCode)
Test Errored (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromTestCode)
strlen(): Argument #1 ($string) must be of type string, null given
Test Finished (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromTestCode)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromFirstParty)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromFirstParty)
Test Errored (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromFirstParty)
strlen(): Argument #1 ($string) must be of type string, null given
Test Finished (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromFirstParty)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromThirdParty)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromThirdParty)
Test Triggered PHP Deprecation (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromThirdParty, issue triggered by third-party code calling into PHP runtime, suppressed using operator) in %sThirdPartyClass.php:%d
strlen(): Passing null to parameter #1 ($string) of type string is deprecated
Test Passed (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromThirdParty)
Test Finished (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest::testFromThirdParty)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PhpDeprecation\PhpDeprecationTest, 3 tests)
Test Suite Finished (default, 3 tests)
Test Suite Finished (%sphpunit.xml, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
