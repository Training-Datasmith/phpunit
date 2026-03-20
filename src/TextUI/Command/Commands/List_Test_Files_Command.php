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
namespace Php_Unit\Text_Ui\Command;

use function array_unique;
use function assert;
use const PHP_EOL;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use ReflectionClass;
use Reflection_Exception;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class List_Test_Files_Command implements Command
{
    /**
     * @param list<PhptTestCase|TestCase> $tests
     */
    public function __construct(private array $tests)
    {
    }
    /**
     * @throws ReflectionException
     */
    public function execute(): Result
    {
        $buffer = 'Available test files:' . PHP_EOL;
        $results = [];
        foreach ($this->tests as $test) {
            if ($test instanceof Test_Case) {
                $name = (new ReflectionClass($test))->get_file_name();
                assert($name !== false);
                $results[] = $name;
                continue;
            }
            $results[] = $test->get_name();
        }
        foreach (array_unique($results) as $result) {
            $buffer .= sprintf(' - %s' . PHP_EOL, $result);
        }
        return Result::from($buffer);
    }
}