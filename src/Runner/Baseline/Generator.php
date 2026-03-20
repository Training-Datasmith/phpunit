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
namespace Php_Unit\Runner\Baseline;

use Php_Unit\Event\Facade;
use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Php_Deprecation_Triggered;
use Php_Unit\Event\Test\Php_Notice_Triggered;
use Php_Unit\Event\Test\Php_Warning_Triggered;
use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Runner\File_Does_Not_Exist_Exception;
use Php_Unit\Text_Ui\Configuration\Source;
use Php_Unit\Text_Ui\Configuration\Source_Filter;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Generator
{
    private Baseline $baseline;
    public function __construct(Facade $facade, private Source $source)
    {
        $facade->register_subscribers(new Test_Triggered_Deprecation_Subscriber($this), new Test_Triggered_Notice_Subscriber($this), new Test_Triggered_Php_Deprecation_Subscriber($this), new Test_Triggered_Php_Notice_Subscriber($this), new Test_Triggered_Php_Warning_Subscriber($this), new Test_Triggered_Warning_Subscriber($this));
        $this->baseline = new Baseline();
    }
    public function baseline(): Baseline
    {
        return $this->baseline;
    }
    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function test_triggered_issue(Deprecation_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Php_Warning_Triggered|Warning_Triggered $event): void
    {
        if ($event->was_suppressed() && !$this->is_suppression_ignored($event)) {
            return;
        }
        if ($this->restrict($event) && !Source_Filter::instance()->includes($event->file())) {
            return;
        }
        $this->baseline->add(Issue::from($event->file(), $event->line(), null, $event->message()));
    }
    private function restrict(Deprecation_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Php_Warning_Triggered|Warning_Triggered $event): bool
    {
        if ($event instanceof Warning_Triggered || $event instanceof Php_Warning_Triggered) {
            return $this->source->restrict_warnings();
        }
        if ($event instanceof Notice_Triggered || $event instanceof Php_Notice_Triggered) {
            return $this->source->restrict_notices();
        }
        return false;
    }
    private function is_suppression_ignored(Deprecation_Triggered|Notice_Triggered|Php_Deprecation_Triggered|Php_Notice_Triggered|Php_Warning_Triggered|Warning_Triggered $event): bool
    {
        if ($event instanceof Warning_Triggered) {
            return $this->source->ignore_suppression_of_warnings();
        }
        if ($event instanceof Php_Warning_Triggered) {
            return $this->source->ignore_suppression_of_php_warnings();
        }
        if ($event instanceof Php_Notice_Triggered) {
            return $this->source->ignore_suppression_of_php_notices();
        }
        if ($event instanceof Notice_Triggered) {
            return $this->source->ignore_suppression_of_notices();
        }
        if ($event instanceof Php_Deprecation_Triggered) {
            return $this->source->ignore_suppression_of_php_deprecations();
        }
        return $this->source->ignore_suppression_of_deprecations();
    }
}