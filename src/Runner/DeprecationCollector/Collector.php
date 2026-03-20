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

use Php_Unit\Event\Facade;
use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Test_Runner\Issue_Filter;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collector
{
    /**
     * @var list<non-empty-string>
     */
    private array $deprecations = [];
    /**
     * @var list<non-empty-string>
     */
    private array $filtered_deprecations = [];
    public function __construct(Facade $facade, private readonly Issue_Filter $issue_filter)
    {
        $facade->register_subscribers(new Test_Prepared_Subscriber($this), new Test_Triggered_Deprecation_Subscriber($this));
    }
    /**
     * @return list<non-empty-string>
     */
    public function deprecations(): array
    {
        return $this->deprecations;
    }
    /**
     * @return list<non-empty-string>
     */
    public function filtered_deprecations(): array
    {
        return $this->filtered_deprecations;
    }
    public function test_prepared(): void
    {
        $this->deprecations = [];
    }
    public function test_triggered_deprecation(Deprecation_Triggered $event): void
    {
        $this->deprecations[] = $event->message();
        if (!$this->issue_filter->should_be_processed($event)) {
            return;
        }
        $this->filtered_deprecations[] = $event->message();
    }
}