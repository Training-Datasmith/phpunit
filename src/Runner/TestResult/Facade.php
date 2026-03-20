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
namespace Php_Unit\Test_Runner\Test_Result;

use function array_any;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Runner\Deprecation_Collector\Facade as DeprecationCollectorFacade;
use Php_Unit\Test_Runner\Issue_Filter;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use function str_contains;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Collector $collector = null;
    public static function init(): void
    {
        self::collector();
    }
    public static function result(): Test_Result
    {
        return self::collector()->result();
    }
    public static function should_stop(): bool
    {
        $configuration = Configuration_Registry::get();
        $collector = self::collector();
        if (($configuration->stop_on_defect() || $configuration->stop_on_error()) && $collector->has_errored_tests()) {
            return true;
        }
        if (($configuration->stop_on_defect() || $configuration->stop_on_failure()) && $collector->has_failed_tests()) {
            return true;
        }
        if (($configuration->stop_on_defect() || $configuration->stop_on_warning()) && $collector->has_warnings()) {
            return true;
        }
        if (($configuration->stop_on_defect() || $configuration->stop_on_risky()) && $collector->has_risky_tests()) {
            return true;
        }
        if (self::stop_on_deprecation($configuration)) {
            return true;
        }
        if ($configuration->stop_on_notice() && $collector->has_notices()) {
            return true;
        }
        if ($configuration->stop_on_incomplete() && $collector->has_incomplete_tests()) {
            return true;
        }
        if ($configuration->stop_on_skipped() && $collector->has_skipped_tests()) {
            return true;
        }
        return false;
    }
    private static function collector(): Collector
    {
        if (self::$collector === null) {
            $configuration = Configuration_Registry::get();
            self::$collector = new Collector(Event_Facade::instance(), new Issue_Filter($configuration->source()));
        }
        return self::$collector;
    }
    private static function stop_on_deprecation(Configuration $configuration): bool
    {
        if (!$configuration->stop_on_deprecation()) {
            return false;
        }
        $deprecations = Deprecation_Collector_Facade::filtered_deprecations();
        if (!$configuration->has_specific_deprecation_to_stop_on()) {
            return $deprecations !== [];
        }
        return array_any($deprecations, static fn(string $deprecation): bool => str_contains($deprecation, $configuration->specific_deprecation_to_stop_on()));
    }
}