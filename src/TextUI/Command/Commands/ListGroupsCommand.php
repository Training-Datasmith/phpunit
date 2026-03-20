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

use function count;
use function ksort;
use const PHP_EOL;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use function sprintf;
use function str_starts_with;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class List_Groups_Command implements Command
{
    /**
     * @param list<PhptTestCase|TestCase> $tests
     */
    public function __construct(private array $tests)
    {
    }
    public function execute(): Result
    {
        /** @var array<non-empty-string, positive-int> $groups */
        $groups = [];
        foreach ($this->tests as $test) {
            if ($test instanceof Phpt_Test_Case) {
                $_groups = ['default'];
            } else {
                $_groups = $test->groups();
            }
            foreach ($_groups as $group) {
                if (!isset($groups[$group])) {
                    $groups[$group] = 1;
                } else {
                    $groups[$group]++;
                }
            }
        }
        ksort($groups);
        $buffer = sprintf('Available test group%s:' . PHP_EOL, count($groups) > 1 ? 's' : '');
        foreach ($groups as $group => $number_of_tests) {
            if (str_starts_with((string) $group, '__phpunit_')) {
                continue;
            }
            $buffer .= sprintf(' - %s (%d test%s)' . PHP_EOL, (string) $group, $number_of_tests, $number_of_tests > 1 ? 's' : '');
        }
        return Result::from($buffer);
    }
}