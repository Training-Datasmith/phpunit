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
namespace Php_Unit\Test_Runner;

use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Error_Triggered;
use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Php_Deprecation_Triggered;
use Php_Unit\Event\Test\Php_Notice_Triggered;
use Php_Unit\Event\Test\Php_Warning_Triggered;
use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Text_Ui\Configuration\Source;
use Php_Unit\Text_Ui\Configuration\Source_Filter;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Issue_Filter
{
    public function __construct(private Source $source)
    {
    }
    public function should_be_processed(Deprecation_Triggered|Error_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Php_Warning_Triggered|Warning_Triggered $event, bool $only_test_methods = false): bool
    {
        if ($only_test_methods && !$event->test()->is_test_method()) {
            return false;
        }
        if ($event instanceof Deprecation_Triggered || $event instanceof Php_Deprecation_Triggered) {
            if ($event->ignored_by_test()) {
                return false;
            }
            if ($this->source->ignore_self_deprecations() && $event->trigger()->is_self()) {
                return false;
            }
            if ($this->source->ignore_direct_deprecations() && $event->trigger()->is_direct()) {
                return false;
            }
            if ($this->source->ignore_indirect_deprecations() && $event->trigger()->is_indirect()) {
                return false;
            }
            if (!$this->source->ignore_suppression_of_deprecations() && $event->was_suppressed()) {
                return false;
            }
        }
        if ($event instanceof Notice_Triggered) {
            if (!$this->source->ignore_suppression_of_notices() && $event->was_suppressed()) {
                return false;
            }
            if ($this->source->restrict_notices() && !Source_Filter::instance()->includes($event->file())) {
                return false;
            }
        }
        if ($event instanceof Php_Notice_Triggered) {
            if (!$this->source->ignore_suppression_of_php_notices() && $event->was_suppressed()) {
                return false;
            }
            if ($this->source->restrict_notices() && !Source_Filter::instance()->includes($event->file())) {
                return false;
            }
        }
        if ($event instanceof Warning_Triggered) {
            if (!$this->source->ignore_suppression_of_warnings() && $event->was_suppressed()) {
                return false;
            }
            if ($this->source->restrict_warnings() && !Source_Filter::instance()->includes($event->file())) {
                return false;
            }
        }
        if ($event instanceof Php_Warning_Triggered) {
            if (!$this->source->ignore_suppression_of_php_warnings() && $event->was_suppressed()) {
                return false;
            }
            if ($this->source->restrict_warnings() && !Source_Filter::instance()->includes($event->file())) {
                return false;
            }
        }
        if ($event instanceof Error_Triggered) {
            if (!$this->source->ignore_suppression_of_errors() && $event->was_suppressed()) {
                return false;
            }
        }
        return true;
    }
}