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

use Php_Unit\Event\Event_Facade_Is_Sealed_Exception;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Event\Subscriber;
use Php_Unit\Event\Tracer\Tracer;
use Php_Unit\Event\Unknown_Subscriber_Type_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private bool $replaces_output = false;
    private bool $replaces_progress_output = false;
    private bool $replaces_result_output = false;
    private bool $requires_code_coverage_collection = false;
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function register_subscribers(Subscriber ...$subscribers): void
    {
        Event_Facade::instance()->register_subscribers(...$subscribers);
    }
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function register_subscriber(Subscriber $subscriber): void
    {
        Event_Facade::instance()->register_subscriber($subscriber);
    }
    /**
     * @throws EventFacadeIsSealedException
     */
    public function register_tracer(Tracer $tracer): void
    {
        Event_Facade::instance()->register_tracer($tracer);
    }
    public function replace_output(): void
    {
        $this->replaces_output = true;
    }
    public function replaces_output(): bool
    {
        return $this->replaces_output;
    }
    public function replace_progress_output(): void
    {
        $this->replaces_progress_output = true;
    }
    public function replaces_progress_output(): bool
    {
        return $this->replaces_output || $this->replaces_progress_output;
    }
    public function replace_result_output(): void
    {
        $this->replaces_result_output = true;
    }
    public function replaces_result_output(): bool
    {
        return $this->replaces_output || $this->replaces_result_output;
    }
    public function require_code_coverage_collection(): void
    {
        $this->requires_code_coverage_collection = true;
    }
    public function requires_code_coverage_collection(): bool
    {
        return $this->requires_code_coverage_collection;
    }
}