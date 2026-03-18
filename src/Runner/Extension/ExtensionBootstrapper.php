<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Runner\Extension;

use function assert;
use function class_exists;
use function class_implements;
use function in_array;

use const PHP_EOL;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;

use function sprintf;

use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExtensionBootstrapper
{
    public function __construct(private Configuration $configuration, private Facade $facade)
    {
    }

    /**
     * @param non-empty-string      $className
     * @param array<string, string> $parameters
     */
    public function bootstrap(string $className, array $parameters): void
    {
        if (!class_exists($className)) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Cannot bootstrap extension because class %s does not exist',
                    $className,
                ),
            );

            return;
        }

        if (!in_array(Extension::class, class_implements($className), true)) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Cannot bootstrap extension because class %s does not implement interface %s',
                    $className,
                    Extension::class,
                ),
            );

            return;
        }

        try {
            $instance = new ReflectionClass($className)->newInstance();

            assert($instance instanceof Extension);

            $instance->bootstrap(
                $this->configuration,
                $this->facade,
                ParameterCollection::fromArray($parameters),
            );
        } catch (Throwable $t) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Bootstrapping of extension %s failed: %s%s%s',
                    $className,
                    $t->getMessage(),
                    PHP_EOL,
                    $t->getTraceAsString(),
                ),
            );

            return;
        }

        EventFacade::emitter()->testRunnerBootstrappedExtension(
            $className,
            $parameters,
        );
    }
}
