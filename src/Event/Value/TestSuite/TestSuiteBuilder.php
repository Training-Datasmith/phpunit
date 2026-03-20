<?php

declare (strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Php_Unit\Event\Test_Suite;

use function assert;
use function class_exists;
use function count;
use function explode;
use function method_exists;
use Php_Unit\Event\Code\Test;
use Php_Unit\Event\Code\Test_Collection;
use Php_Unit\Event\RuntimeException;
use Php_Unit\Framework\Data_Provider_Test_Suite;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Framework\Test_Suite as FrameworkTestSuite;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use ReflectionClass;
use ReflectionMethod;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Suite_Builder
{
    /**
     * @throws RuntimeException
     */
    public static function from(Framework_Test_Suite $test_suite): Test_Suite
    {
        $tests = [];
        self::process($test_suite, $tests);
        if ($test_suite instanceof Data_Provider_Test_Suite) {
            assert(count(explode('::', $test_suite->name())) === 2);
            [$class_name, $method_name] = explode('::', $test_suite->name());
            assert(class_exists($class_name));
            assert($method_name !== '' && method_exists($class_name, $method_name));
            $reflector = new ReflectionMethod($class_name, $method_name);
            $file = $reflector->get_file_name();
            $line = $reflector->get_start_line();
            assert($file !== false);
            assert($line !== false);
            return new Test_Suite_For_Test_Method_With_Data_Provider($test_suite->name(), $test_suite->count(), Test_Collection::from_array($tests), $class_name, $method_name, $file, $line);
        }
        if ($test_suite->is_for_test_class()) {
            $test_class_name = $test_suite->name();
            assert(class_exists($test_class_name));
            $reflector = new ReflectionClass($test_class_name);
            $file = $reflector->get_file_name();
            $line = $reflector->get_start_line();
            assert($file !== false);
            assert($line !== false);
            return new Test_Suite_For_Test_Class($test_class_name, $test_suite->count(), Test_Collection::from_array($tests), $file, $line);
        }
        return new Test_Suite_With_Name($test_suite->name(), $test_suite->count(), Test_Collection::from_array($tests));
    }
    /**
     * @param list<Test> $tests
     */
    private static function process(Framework_Test_Suite $test_suite, array &$tests): void
    {
        foreach ($test_suite->getIterator() as $test) {
            if ($test instanceof Framework_Test_Suite) {
                self::process($test, $tests);
                continue;
            }
            if ($test instanceof Test_Case || $test instanceof Phpt_Test_Case) {
                $tests[] = $test->value_object_for_events();
            }
        }
    }
}