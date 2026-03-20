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
namespace Php_Unit\Runner\Deprecation_Collector;

use Php_Unit\Event\Event_Facade_Is_Sealed_Exception;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Event\Unknown_Subscriber_Type_Exception;
use Php_Unit\Test_Runner\Issue_Filter;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static null|Collector|In_Isolation_Collector $collector = null;
    private static bool $in_isolation = false;
    public static function init(): void
    {
        self::collector();
    }
    public static function init_for_isolation(): void
    {
        self::collector();
        self::$in_isolation = true;
    }
    /**
     * @return list<non-empty-string>
     */
    public static function deprecations(): array
    {
        return self::collector()->deprecations();
    }
    /**
     * @return list<non-empty-string>
     */
    public static function filtered_deprecations(): array
    {
        return self::collector()->filtered_deprecations();
    }
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function collector(): Collector|In_Isolation_Collector
    {
        if (self::$collector !== null) {
            return self::$collector;
        }
        $issue_filter = new Issue_Filter(Configuration_Registry::get()->source());
        if (self::$in_isolation) {
            self::$collector = new In_Isolation_Collector($issue_filter);
            return self::$collector;
        }
        self::$collector = new Collector(Event_Facade::instance(), $issue_filter);
        return self::$collector;
    }
}