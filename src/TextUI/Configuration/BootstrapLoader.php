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
namespace Php_Unit\Text_Ui\Configuration;

use function in_array;
use function is_readable;
use const PHP_EOL;
use Php_Unit\Event\Facade as EventFacade;
use function sprintf;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Bootstrap_Loader
{
    /**
     * @throws BootstrapScriptDoesNotExistException
     * @throws BootstrapScriptException
     */
    public function handle(Configuration $configuration): void
    {
        if (!$configuration->has_bootstrap()) {
            return;
        }
        $this->load($configuration->bootstrap());
        foreach ($configuration->bootstrap_for_test_suite() as $test_suite_name => $bootstrap_for_test_suite) {
            if ($configuration->include_test_suites() !== [] && !in_array($test_suite_name, $configuration->include_test_suites(), true)) {
                continue;
            }
            if ($configuration->exclude_test_suites() !== [] && in_array($test_suite_name, $configuration->exclude_test_suites(), true)) {
                continue;
            }
            $this->load($bootstrap_for_test_suite);
        }
    }
    /**
     * @param non-empty-string $filename
     */
    private function load(string $filename): void
    {
        if (!is_readable($filename)) {
            throw new Bootstrap_Script_Does_Not_Exist_Exception($filename);
        }
        try {
            include_once $filename;
        } catch (Throwable $t) {
            $message = sprintf('Error in bootstrap script: %s:%s%s%s%s', $t::class, PHP_EOL, $t->get_message(), PHP_EOL, $t->get_trace_as_string());
            while ($t = $t->get_previous()) {
                $message .= sprintf('%s%sPrevious error: %s:%s%s%s%s', PHP_EOL, PHP_EOL, $t::class, PHP_EOL, $t->get_message(), PHP_EOL, $t->get_trace_as_string());
            }
            throw new Bootstrap_Script_Exception($message);
        }
        Event_Facade::emitter()->test_runner_bootstrap_finished($filename);
    }
}