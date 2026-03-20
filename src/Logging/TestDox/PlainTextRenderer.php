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
namespace Php_Unit\Logging\Test_Dox;

use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Plain_Text_Renderer
{
    /**
     * @param array<string, TestResultCollection> $tests
     */
    public function render(array $tests): string
    {
        $buffer = '';
        foreach ($tests as $prettified_class_name => $_tests) {
            $buffer .= $prettified_class_name . "\n";
            foreach ($this->reduce($_tests) as $prettified_method_name => $outcome) {
                $buffer .= sprintf(' [%s] %s' . "\n", $outcome, $prettified_method_name);
            }
            $buffer .= "\n";
        }
        return $buffer;
    }
    /**
     * @return array<string, ' '|'x'>
     */
    private function reduce(Test_Result_Collection $tests): array
    {
        $result = [];
        foreach ($tests as $test) {
            $prettified_method_name = $test->test()->test_dox()->prettified_method_name();
            $success = true;
            if ($test->status()->is_error() || $test->status()->is_failure() || $test->status()->is_incomplete() || $test->status()->is_skipped()) {
                $success = false;
            }
            if (!isset($result[$prettified_method_name])) {
                $result[$prettified_method_name] = $success ? 'x' : ' ';
                continue;
            }
            if ($success) {
                continue;
            }
            $result[$prettified_method_name] = ' ';
        }
        return $result;
    }
}