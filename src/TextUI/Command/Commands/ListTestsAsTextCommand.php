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
use const PHP_EOL;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use function sprintf;
use function str_replace;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class List_Tests_As_Text_Command implements Command
{
    /**
     * @param list<PhptTestCase|TestCase> $tests
     */
    public function __construct(private array $tests)
    {
    }
    public function execute(): Result
    {
        $buffer = sprintf('Available test%s:' . PHP_EOL, count($this->tests) > 1 ? 's' : '');
        foreach ($this->tests as $test) {
            if ($test instanceof Test_Case) {
                $name = sprintf('%s::%s', $test::class, str_replace(' with data set ', '', $test->name_with_data_set()));
            } else {
                $name = $test->get_name();
            }
            $buffer .= sprintf(' - %s' . PHP_EOL, $name);
        }
        return Result::from($buffer);
    }
}