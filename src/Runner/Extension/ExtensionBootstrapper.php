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
namespace Php_Unit\Runner\Extension;

use function assert;
use function class_exists;
use function class_implements;
use function in_array;
use const PHP_EOL;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Text_Ui\Configuration\Configuration;
use ReflectionClass;
use function sprintf;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Extension_Bootstrapper
{
    public function __construct(private Configuration $configuration, private Facade $facade)
    {
    }
    /**
     * @param non-empty-string      $className
     * @param array<string, string> $parameters
     */
    public function bootstrap(string $class_name, array $parameters): void
    {
        if (!class_exists($class_name)) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot bootstrap extension because class %s does not exist', $class_name));
            return;
        }
        if (!in_array(Extension::class, class_implements($class_name), true)) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot bootstrap extension because class %s does not implement interface %s', $class_name, Extension::class));
            return;
        }
        try {
            $instance = (new ReflectionClass($class_name))->new_instance();
            assert($instance instanceof Extension);
            $instance->bootstrap($this->configuration, $this->facade, Parameter_Collection::from_array($parameters));
        } catch (Throwable $t) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Bootstrapping of extension %s failed: %s%s%s', $class_name, $t->get_message(), PHP_EOL, $t->get_trace_as_string()));
            return;
        }
        Event_Facade::emitter()->test_runner_bootstrapped_extension($class_name, $parameters);
    }
}