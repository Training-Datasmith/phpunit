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
namespace Php_Unit\Text_Ui;

use function array_map;
use Php_Unit\Event;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Filter\Factory;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Configuration\Filter_Not_Configured_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Suite_Filter_Processor
{
    /**
     * @throws Event\RuntimeException
     * @throws FilterNotConfiguredException
     */
    public function process(Configuration $configuration, Test_Suite $suite): void
    {
        $factory = new Factory();
        if (!$configuration->has_filter() && !$configuration->has_groups() && !$configuration->has_exclude_groups() && !$configuration->has_exclude_filter() && !$configuration->has_tests_covering() && !$configuration->has_tests_using() && !$configuration->has_tests_requiring_php_extension()) {
            return;
        }
        if ($configuration->has_exclude_groups()) {
            $factory->add_exclude_group_filter($configuration->exclude_groups());
        }
        if ($configuration->has_groups()) {
            $factory->add_include_group_filter($configuration->groups());
        }
        if ($configuration->has_tests_covering()) {
            $factory->add_include_group_filter(array_map(static fn(string $name): string => '__phpunit_covers_' . $name, $configuration->tests_covering()));
        }
        if ($configuration->has_tests_using()) {
            $factory->add_include_group_filter(array_map(static fn(string $name): string => '__phpunit_uses_' . $name, $configuration->tests_using()));
        }
        if ($configuration->has_tests_requiring_php_extension()) {
            $factory->add_include_group_filter(array_map(static fn(string $name): string => '__phpunit_requires_php_extension' . $name, $configuration->tests_requiring_php_extension()));
        }
        if ($configuration->has_exclude_filter()) {
            $factory->add_exclude_name_filter($configuration->exclude_filter());
        }
        if ($configuration->has_filter()) {
            $factory->add_include_name_filter($configuration->filter());
        }
        $suite->inject_filter($factory);
        Event\Facade::emitter()->test_suite_filtered(Event\Test_Suite\Test_Suite_Builder::from($suite));
    }
}