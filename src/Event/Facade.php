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
namespace Php_Unit\Event;

use function assert;
use function interface_exists;
use Php_Unit\Event\Telemetry\Hr_Time;
use Php_Unit\Event\Telemetry\System_Garbage_Collector_Status_Provider;
use Php_Unit\Runner\Deprecation_Collector\Facade as DeprecationCollector;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?self $instance = null;
    private Emitter $emitter;
    private ?Type_Map $type_map = null;
    private ?Deferring_Dispatcher $deferring_dispatcher = null;
    private bool $sealed = false;
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public static function emitter(): Emitter
    {
        return self::instance()->emitter;
    }
    public function __construct()
    {
        $this->emitter = $this->create_dispatching_emitter();
    }
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function register_subscribers(Subscriber ...$subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->register_subscriber($subscriber);
        }
    }
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function register_subscriber(Subscriber $subscriber): void
    {
        if ($this->sealed) {
            throw new Event_Facade_Is_Sealed_Exception();
        }
        $this->deferred_dispatcher()->register_subscriber($subscriber);
    }
    /**
     * @throws EventFacadeIsSealedException
     */
    public function register_tracer(Tracer\Tracer $tracer): void
    {
        if ($this->sealed) {
            throw new Event_Facade_Is_Sealed_Exception();
        }
        $this->deferred_dispatcher()->register_tracer($tracer);
    }
    /**
     * @codeCoverageIgnore
     *
     * @noinspection PhpUnused
     */
    public function init_for_isolation(Hr_Time $offset): Collecting_Dispatcher
    {
        Deprecation_Collector::init_for_isolation();
        $dispatcher = new Collecting_Dispatcher(new Direct_Dispatcher($this->type_map()));
        $this->emitter = new Dispatching_Emitter($dispatcher, new Telemetry\System(new Telemetry\System_Stop_Watch_With_Offset($offset), new Telemetry\System_Memory_Meter(), new System_Garbage_Collector_Status_Provider()));
        $this->sealed = true;
        return $dispatcher;
    }
    public function forward(Event_Collection $events): void
    {
        $dispatcher = $this->deferred_dispatcher();
        foreach ($events as $event) {
            $dispatcher->dispatch($event);
        }
    }
    public function seal(): void
    {
        $this->deferred_dispatcher()->flush();
        $this->sealed = true;
        $this->emitter->test_runner_event_facade_sealed();
    }
    private function create_dispatching_emitter(): Dispatching_Emitter
    {
        return new Dispatching_Emitter($this->deferred_dispatcher(), $this->create_telemetry_system());
    }
    private function create_telemetry_system(): Telemetry\System
    {
        return new Telemetry\System(new Telemetry\System_Stop_Watch(), new Telemetry\System_Memory_Meter(), new System_Garbage_Collector_Status_Provider());
    }
    private function deferred_dispatcher(): Deferring_Dispatcher
    {
        if ($this->deferring_dispatcher === null) {
            $this->deferring_dispatcher = new Deferring_Dispatcher(new Direct_Dispatcher($this->type_map()));
        }
        return $this->deferring_dispatcher;
    }
    private function type_map(): Type_Map
    {
        if ($this->type_map === null) {
            $type_map = new Type_Map();
            $this->register_default_types($type_map);
            $this->type_map = $type_map;
        }
        return $this->type_map;
    }
    private function register_default_types(Type_Map $type_map): void
    {
        $default_events = [Application\Started::class, Application\Finished::class, Test\Data_Provider_Method_Called::class, Test\Data_Provider_Method_Finished::class, Test\Marked_Incomplete::class, Test\After_Last_Test_Method_Called::class, Test\After_Last_Test_Method_Errored::class, Test\After_Last_Test_Method_Failed::class, Test\After_Last_Test_Method_Finished::class, Test\After_Test_Method_Called::class, Test\After_Test_Method_Errored::class, Test\After_Test_Method_Failed::class, Test\After_Test_Method_Finished::class, Test\Before_First_Test_Method_Called::class, Test\Before_First_Test_Method_Errored::class, Test\Before_First_Test_Method_Failed::class, Test\Before_First_Test_Method_Finished::class, Test\Before_Test_Method_Called::class, Test\Before_Test_Method_Errored::class, Test\Before_Test_Method_Failed::class, Test\Before_Test_Method_Finished::class, Test\Additional_Information_Provided::class, Test\Comparator_Registered::class, Test\Custom_Test_Method_Invocation_Used::class, Test\Considered_Risky::class, Test\Deprecation_Triggered::class, Test\Errored::class, Test\Error_Triggered::class, Test\Failed::class, Test\Finished::class, Test\Notice_Triggered::class, Test\Passed::class, Test\Php_Deprecation_Triggered::class, Test\Php_Notice_Triggered::class, Test\Phpunit_Deprecation_Triggered::class, Test\Phpunit_Notice_Triggered::class, Test\Phpunit_Error_Triggered::class, Test\Phpunit_Warning_Triggered::class, Test\Php_Warning_Triggered::class, Test\Post_Condition_Called::class, Test\Post_Condition_Errored::class, Test\Post_Condition_Failed::class, Test\Post_Condition_Finished::class, Test\Pre_Condition_Called::class, Test\Pre_Condition_Errored::class, Test\Pre_Condition_Failed::class, Test\Pre_Condition_Finished::class, Test\Preparation_Started::class, Test\Prepared::class, Test\Preparation_Errored::class, Test\Preparation_Failed::class, Test\Printed_Unexpected_Output::class, Test\Skipped::class, Test\Warning_Triggered::class, Test\Mock_Object_Created::class, Test\Mock_Object_For_Intersection_Of_Interfaces_Created::class, Test\Partial_Mock_Object_Created::class, Test\Test_Stub_Created::class, Test\Test_Stub_For_Intersection_Of_Interfaces_Created::class, Test_Runner\Bootstrap_Finished::class, Test_Runner\Configured::class, Test_Runner\Event_Facade_Sealed::class, Test_Runner\Execution_Aborted::class, Test_Runner\Execution_Finished::class, Test_Runner\Execution_Started::class, Test_Runner\Extension_Loaded_From_Phar::class, Test_Runner\Extension_Bootstrapped::class, Test_Runner\Finished::class, Test_Runner\Started::class, Test_Runner\Deprecation_Triggered::class, Test_Runner\Notice_Triggered::class, Test_Runner\Warning_Triggered::class, Test_Runner\Garbage_Collection_Disabled::class, Test_Runner\Garbage_Collection_Triggered::class, Test_Runner\Garbage_Collection_Enabled::class, Test_Runner\Child_Process_Started::class, Test_Runner\Child_Process_Errored::class, Test_Runner\Child_Process_Finished::class, Test_Runner\Static_Analysis_For_Code_Coverage_Finished::class, Test_Runner\Static_Analysis_For_Code_Coverage_Started::class, Test_Suite\Filtered::class, Test_Suite\Finished::class, Test_Suite\Loaded::class, Test_Suite\Skipped::class, Test_Suite\Sorted::class, Test_Suite\Started::class];
        foreach ($default_events as $event_class) {
            $subscriber_interface = $event_class . 'Subscriber';
            assert(interface_exists($subscriber_interface));
            $type_map->add_mapping($subscriber_interface, $event_class);
        }
    }
}