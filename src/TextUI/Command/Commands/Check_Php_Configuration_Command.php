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

use function assert;
use const E_ALL;
use function extension_loaded;
use function in_array;
use function ini_get;
use function max;
use const PHP_EOL;
use Php_Unit\Runner\Version;
use Php_Unit\Util\Color;
use Sebastian_Bergmann\Environment\Console;
use function sprintf;
use function strlen;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Check_Php_Configuration_Command implements Command
{
    private bool $colorize;
    public function __construct()
    {
        $this->colorize = (new Console())->has_color_support();
    }
    public function execute(): Result
    {
        $lines = [];
        $shell_exit_code = 0;
        foreach ($this->settings() as $name => $setting) {
            foreach ($setting['requiredExtensions'] as $extension) {
                if (!extension_loaded($extension)) {
                    // @codeCoverageIgnoreStart
                    continue 2;
                    // @codeCoverageIgnoreEnd
                }
            }
            $actual_value = ini_get($name);
            if (in_array($actual_value, $setting['expectedValues'], true)) {
                $check = $this->ok();
            } else {
                $check = $this->not_ok($actual_value);
                $shell_exit_code = 1;
            }
            $lines[] = [sprintf('%s = %s', $name, $setting['valueForConfiguration']), $check];
        }
        $max_length = 0;
        foreach ($lines as $line) {
            $max_length = max($max_length, strlen($line[0]));
        }
        $buffer = sprintf('Checking whether PHP is configured according to https://docs.phpunit.de/en/%s/installation.html#configuring-php-for-development' . PHP_EOL . PHP_EOL, Version::series());
        foreach ($lines as $line) {
            $buffer .= sprintf('%-' . $max_length . 's ... %s' . PHP_EOL, $line[0], $line[1]);
        }
        return Result::from($buffer, $shell_exit_code);
    }
    /**
     * @return non-empty-string
     */
    private function ok(): string
    {
        if (!$this->colorize) {
            return 'ok';
        }
        // @codeCoverageIgnoreStart
        $result = Color::colorize_text_box('fg-green, bold', 'ok');
        assert($result !== '');
        return $result;
        // @codeCoverageIgnoreEnd
    }
    /**
     * @return non-empty-string
     */
    private function not_ok(string $actual_value): string
    {
        $message = sprintf('not ok (%s)', $actual_value);
        if (!$this->colorize) {
            return $message;
        }
        // @codeCoverageIgnoreStart
        $result = Color::colorize_text_box('fg-red, bold', $message);
        assert($result !== '');
        return $result;
        // @codeCoverageIgnoreEnd
    }
    /**
     * @return non-empty-array<non-empty-string, array{expectedValues: non-empty-list<non-empty-string>, valueForConfiguration: non-empty-string, requiredExtensions: list<non-empty-string>}>
     */
    private function settings(): array
    {
        return ['display_errors' => ['expectedValues' => ['1'], 'valueForConfiguration' => 'On', 'requiredExtensions' => []], 'display_startup_errors' => ['expectedValues' => ['1'], 'valueForConfiguration' => 'On', 'requiredExtensions' => []], 'error_reporting' => ['expectedValues' => ['-1', (string) E_ALL], 'valueForConfiguration' => '-1', 'requiredExtensions' => []], 'xdebug.show_exception_trace' => ['expectedValues' => ['0'], 'valueForConfiguration' => '0', 'requiredExtensions' => ['xdebug']], 'zend.assertions' => ['expectedValues' => ['1'], 'valueForConfiguration' => '1', 'requiredExtensions' => []], 'assert.exception' => ['expectedValues' => ['1'], 'valueForConfiguration' => '1', 'requiredExtensions' => []], 'memory_limit' => ['expectedValues' => ['-1'], 'valueForConfiguration' => '-1', 'requiredExtensions' => []]];
    }
}