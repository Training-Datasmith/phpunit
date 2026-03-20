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
namespace Php_Unit\Metadata\Parser;

use function assert;
use function class_exists;
use Error;
use function is_numeric;
use function json_decode;
use const JSON_THROW_ON_ERROR;
use function method_exists;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Attributes\After;
use Php_Unit\Framework\Attributes\After_Class;
use Php_Unit\Framework\Attributes\Allow_Mock_Objects_Without_Expectations;
use Php_Unit\Framework\Attributes\Backup_Globals;
use Php_Unit\Framework\Attributes\Backup_Static_Properties;
use Php_Unit\Framework\Attributes\Before;
use Php_Unit\Framework\Attributes\Before_Class;
use Php_Unit\Framework\Attributes\Covers_Class;
use Php_Unit\Framework\Attributes\Covers_Classes_That_Extend_Class;
use Php_Unit\Framework\Attributes\Covers_Classes_That_Implement_Interface;
use Php_Unit\Framework\Attributes\Covers_Function;
use Php_Unit\Framework\Attributes\Covers_Method;
use Php_Unit\Framework\Attributes\Covers_Namespace;
use Php_Unit\Framework\Attributes\Covers_Nothing;
use Php_Unit\Framework\Attributes\Covers_Trait;
use Php_Unit\Framework\Attributes\Data_Provider;
use Php_Unit\Framework\Attributes\Data_Provider_Closure;
use Php_Unit\Framework\Attributes\Data_Provider_External;
use Php_Unit\Framework\Attributes\Depends;
use Php_Unit\Framework\Attributes\Depends_External;
use Php_Unit\Framework\Attributes\Depends_External_Using_Deep_Clone;
use Php_Unit\Framework\Attributes\Depends_External_Using_Shallow_Clone;
use Php_Unit\Framework\Attributes\Depends_On_Class;
use Php_Unit\Framework\Attributes\Depends_On_Class_Using_Deep_Clone;
use Php_Unit\Framework\Attributes\Depends_On_Class_Using_Shallow_Clone;
use Php_Unit\Framework\Attributes\Depends_Using_Deep_Clone;
use Php_Unit\Framework\Attributes\Depends_Using_Shallow_Clone;
use Php_Unit\Framework\Attributes\Disable_Return_Value_Generation_For_Test_Doubles;
use Php_Unit\Framework\Attributes\Does_Not_Perform_Assertions;
use Php_Unit\Framework\Attributes\Exclude_Global_Variable_From_Backup;
use Php_Unit\Framework\Attributes\Exclude_Static_Property_From_Backup;
use Php_Unit\Framework\Attributes\Group;
use Php_Unit\Framework\Attributes\Ignore_Deprecations;
use Php_Unit\Framework\Attributes\Ignore_Phpunit_Deprecations;
use Php_Unit\Framework\Attributes\Ignore_Phpunit_Warnings;
use Php_Unit\Framework\Attributes\Large;
use Php_Unit\Framework\Attributes\Medium;
use Php_Unit\Framework\Attributes\Post_Condition;
use Php_Unit\Framework\Attributes\Pre_Condition;
use Php_Unit\Framework\Attributes\Preserve_Global_State;
use Php_Unit\Framework\Attributes\Requires_Environment_Variable;
use Php_Unit\Framework\Attributes\Requires_Function;
use Php_Unit\Framework\Attributes\Requires_Method;
use Php_Unit\Framework\Attributes\Requires_Operating_System;
use Php_Unit\Framework\Attributes\Requires_Operating_System_Family;
use Php_Unit\Framework\Attributes\Requires_Php;
use Php_Unit\Framework\Attributes\Requires_Php_Extension;
use Php_Unit\Framework\Attributes\Requires_Phpunit;
use Php_Unit\Framework\Attributes\Requires_Phpunit_Extension;
use Php_Unit\Framework\Attributes\Requires_Setting;
use Php_Unit\Framework\Attributes\Run_In_Separate_Process;
use Php_Unit\Framework\Attributes\Run_Tests_In_Separate_Processes;
use Php_Unit\Framework\Attributes\Small;
use Php_Unit\Framework\Attributes\Test;
use Php_Unit\Framework\Attributes\Test_Dox;
use Php_Unit\Framework\Attributes\Test_Dox_Formatter;
use Php_Unit\Framework\Attributes\Test_Dox_Formatter_External;
use Php_Unit\Framework\Attributes\Test_With;
use Php_Unit\Framework\Attributes\Test_With_Json;
use Php_Unit\Framework\Attributes\Ticket;
use Php_Unit\Framework\Attributes\Uses_Class;
use Php_Unit\Framework\Attributes\Uses_Classes_That_Extend_Class;
use Php_Unit\Framework\Attributes\Uses_Classes_That_Implement_Interface;
use Php_Unit\Framework\Attributes\Uses_Function;
use Php_Unit\Framework\Attributes\Uses_Method;
use Php_Unit\Framework\Attributes\Uses_Namespace;
use Php_Unit\Framework\Attributes\Uses_Trait;
use Php_Unit\Framework\Attributes\With_Environment_Variable;
use Php_Unit\Framework\Attributes\Without_Error_Handler;
use Php_Unit\Metadata\Invalid_Attribute_Exception;
use Php_Unit\Metadata\Metadata;
use Php_Unit\Metadata\Metadata_Collection;
use Php_Unit\Metadata\Version\Requirement;
use ReflectionClass;
use ReflectionMethod;
use function sprintf;
use function str_starts_with;
use function strtolower;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Attribute_Parser implements Parser
{
    /**
     * @param class-string $className
     */
    public function for_class(string $class_name): Metadata_Collection
    {
        assert(class_exists($class_name));
        $reflector = new ReflectionClass($class_name);
        $result = [];
        $small = false;
        $medium = false;
        $large = false;
        foreach ($reflector->get_attributes() as $attribute) {
            if (!str_starts_with($attribute->get_name(), 'PHPUnit\Framework\Attributes\\')) {
                continue;
            }
            if (!class_exists($attribute->get_name())) {
                continue;
            }
            try {
                $attribute_instance = $attribute->new_instance();
            } catch (Error $e) {
                throw new Invalid_Attribute_Exception($attribute->get_name(), 'class ' . $class_name, $reflector->get_file_name(), $reflector->get_start_line(), $e->get_message());
            }
            switch ($attribute->get_name()) {
                case Allow_Mock_Objects_Without_Expectations::class:
                    assert($attribute_instance instanceof Allow_Mock_Objects_Without_Expectations);
                    $result[] = Metadata::allow_mock_objects_without_expectations_on_class();
                    break;
                case Backup_Globals::class:
                    assert($attribute_instance instanceof Backup_Globals);
                    $result[] = Metadata::backup_globals_on_class($attribute_instance->enabled());
                    break;
                case Backup_Static_Properties::class:
                    assert($attribute_instance instanceof Backup_Static_Properties);
                    $result[] = Metadata::backup_static_properties_on_class($attribute_instance->enabled());
                    break;
                case Covers_Namespace::class:
                    assert($attribute_instance instanceof Covers_Namespace);
                    $result[] = Metadata::covers_namespace($attribute_instance->namespace());
                    break;
                case Covers_Class::class:
                    assert($attribute_instance instanceof Covers_Class);
                    $result[] = Metadata::covers_class($attribute_instance->class_name());
                    break;
                case Covers_Classes_That_Extend_Class::class:
                    assert($attribute_instance instanceof Covers_Classes_That_Extend_Class);
                    $result[] = Metadata::covers_classes_that_extend_class($attribute_instance->class_name());
                    break;
                case Covers_Classes_That_Implement_Interface::class:
                    assert($attribute_instance instanceof Covers_Classes_That_Implement_Interface);
                    $result[] = Metadata::covers_classes_that_implement_interface($attribute_instance->interface_name());
                    break;
                case Covers_Trait::class:
                    assert($attribute_instance instanceof Covers_Trait);
                    $result[] = Metadata::covers_trait($attribute_instance->trait_name());
                    break;
                case Covers_Function::class:
                    assert($attribute_instance instanceof Covers_Function);
                    $result[] = Metadata::covers_function($attribute_instance->function_name());
                    break;
                case Covers_Method::class:
                    assert($attribute_instance instanceof Covers_Method);
                    $result[] = Metadata::covers_method($attribute_instance->class_name(), $attribute_instance->method_name());
                    break;
                case Covers_Nothing::class:
                    $result[] = Metadata::covers_nothing_on_class();
                    break;
                case Disable_Return_Value_Generation_For_Test_Doubles::class:
                    $result[] = Metadata::disable_return_value_generation_for_test_doubles();
                    break;
                case Does_Not_Perform_Assertions::class:
                    $result[] = Metadata::does_not_perform_assertions_on_class();
                    break;
                case Exclude_Global_Variable_From_Backup::class:
                    assert($attribute_instance instanceof Exclude_Global_Variable_From_Backup);
                    $result[] = Metadata::exclude_global_variable_from_backup_on_class($attribute_instance->global_variable_name());
                    break;
                case Exclude_Static_Property_From_Backup::class:
                    assert($attribute_instance instanceof Exclude_Static_Property_From_Backup);
                    $result[] = Metadata::exclude_static_property_from_backup_on_class($attribute_instance->class_name(), $attribute_instance->property_name());
                    break;
                case Group::class:
                    assert($attribute_instance instanceof Group);
                    if (!$this->is_size_group($attribute_instance->name(), $class_name)) {
                        $result[] = Metadata::group_on_class($attribute_instance->name());
                    }
                    break;
                case Small::class:
                    if (!$medium && !$large) {
                        $result[] = Metadata::group_on_class('small');
                        $small = true;
                    } else {
                        Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('#[Small] cannot be combined with #[Medium] or #[Large] for %s', $this->test_as_string($class_name)));
                    }
                    break;
                case Medium::class:
                    if (!$small && !$large) {
                        $result[] = Metadata::group_on_class('medium');
                        $medium = true;
                    } else {
                        Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('#[Medium] cannot be combined with #[Small] or #[Large] for %s', $this->test_as_string($class_name)));
                    }
                    break;
                case Large::class:
                    if (!$small && !$medium) {
                        $result[] = Metadata::group_on_class('large');
                        $large = true;
                    } else {
                        Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('#[Large] cannot be combined with #[Small] or #[Medium] for %s', $this->test_as_string($class_name)));
                    }
                    break;
                case Ignore_Deprecations::class:
                    assert($attribute_instance instanceof Ignore_Deprecations);
                    $result[] = Metadata::ignore_deprecations_on_class($attribute_instance->message_pattern());
                    break;
                case Ignore_Phpunit_Deprecations::class:
                    assert($attribute_instance instanceof Ignore_Phpunit_Deprecations);
                    $result[] = Metadata::ignore_phpunit_deprecations_on_class();
                    break;
                case Preserve_Global_State::class:
                    assert($attribute_instance instanceof Preserve_Global_State);
                    $result[] = Metadata::preserve_global_state_on_class($attribute_instance->enabled());
                    break;
                case Requires_Method::class:
                    assert($attribute_instance instanceof Requires_Method);
                    $result[] = Metadata::requires_method_on_class($attribute_instance->class_name(), $attribute_instance->method_name());
                    break;
                case Requires_Function::class:
                    assert($attribute_instance instanceof Requires_Function);
                    $result[] = Metadata::requires_function_on_class($attribute_instance->function_name());
                    break;
                case Requires_Operating_System::class:
                    assert($attribute_instance instanceof Requires_Operating_System);
                    $result[] = Metadata::requires_operating_system_on_class($attribute_instance->regular_expression());
                    break;
                case Requires_Operating_System_Family::class:
                    assert($attribute_instance instanceof Requires_Operating_System_Family);
                    $result[] = Metadata::requires_operating_system_family_on_class($attribute_instance->operating_system_family());
                    break;
                case Requires_Php::class:
                    assert($attribute_instance instanceof Requires_Php);
                    $requirement = $this->requirement($attribute_instance->version_requirement(), $class_name);
                    if ($requirement !== null) {
                        $result[] = Metadata::requires_php_on_class($requirement);
                    }
                    break;
                case Requires_Php_Extension::class:
                    assert($attribute_instance instanceof Requires_Php_Extension);
                    $version_constraint = null;
                    $version_requirement = $attribute_instance->version_requirement();
                    if ($version_requirement !== null) {
                        $version_constraint = $this->requirement($version_requirement, $class_name);
                    }
                    $result[] = Metadata::requires_php_extension_on_class($attribute_instance->extension(), $version_constraint);
                    break;
                case Requires_Phpunit::class:
                    assert($attribute_instance instanceof Requires_Phpunit);
                    $requirement = $this->requirement($attribute_instance->version_requirement(), $class_name);
                    if ($requirement !== null) {
                        $result[] = Metadata::requires_phpunit_on_class($requirement);
                    }
                    break;
                case Requires_Phpunit_Extension::class:
                    assert($attribute_instance instanceof Requires_Phpunit_Extension);
                    $result[] = Metadata::requires_phpunit_extension_on_class($attribute_instance->extension_class());
                    break;
                case Requires_Environment_Variable::class:
                    assert($attribute_instance instanceof Requires_Environment_Variable);
                    $result[] = Metadata::requires_environment_variable_on_class($attribute_instance->environment_variable_name(), $attribute_instance->value());
                    break;
                case With_Environment_Variable::class:
                    assert($attribute_instance instanceof With_Environment_Variable);
                    $result[] = Metadata::with_environment_variable_on_class($attribute_instance->environment_variable_name(), $attribute_instance->value());
                    break;
                case Requires_Setting::class:
                    assert($attribute_instance instanceof Requires_Setting);
                    $result[] = Metadata::requires_setting_on_class($attribute_instance->setting(), $attribute_instance->value());
                    break;
                case Run_Tests_In_Separate_Processes::class:
                    $result[] = Metadata::run_tests_in_separate_processes();
                    break;
                case Test_Dox::class:
                    assert($attribute_instance instanceof Test_Dox);
                    $result[] = Metadata::test_dox_on_class($attribute_instance->text());
                    break;
                case Ticket::class:
                    assert($attribute_instance instanceof Ticket);
                    $result[] = Metadata::group_on_class($attribute_instance->text());
                    break;
                case Uses_Namespace::class:
                    assert($attribute_instance instanceof Uses_Namespace);
                    $result[] = Metadata::uses_namespace($attribute_instance->namespace());
                    break;
                case Uses_Class::class:
                    assert($attribute_instance instanceof Uses_Class);
                    $result[] = Metadata::uses_class($attribute_instance->class_name());
                    break;
                case Uses_Classes_That_Extend_Class::class:
                    assert($attribute_instance instanceof Uses_Classes_That_Extend_Class);
                    $result[] = Metadata::uses_classes_that_extend_class($attribute_instance->class_name());
                    break;
                case Uses_Classes_That_Implement_Interface::class:
                    assert($attribute_instance instanceof Uses_Classes_That_Implement_Interface);
                    $result[] = Metadata::uses_classes_that_implement_interface($attribute_instance->interface_name());
                    break;
                case Uses_Trait::class:
                    assert($attribute_instance instanceof Uses_Trait);
                    $result[] = Metadata::uses_trait($attribute_instance->trait_name());
                    break;
                case Uses_Function::class:
                    assert($attribute_instance instanceof Uses_Function);
                    $result[] = Metadata::uses_function($attribute_instance->function_name());
                    break;
                case Uses_Method::class:
                    assert($attribute_instance instanceof Uses_Method);
                    $result[] = Metadata::uses_method($attribute_instance->class_name(), $attribute_instance->method_name());
                    break;
            }
        }
        return Metadata_Collection::from_array($result);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function for_method(string $class_name, string $method_name): Metadata_Collection
    {
        assert(class_exists($class_name));
        assert(method_exists($class_name, $method_name));
        $reflector = new ReflectionMethod($class_name, $method_name);
        $result = [];
        foreach ($reflector->get_attributes() as $attribute) {
            if (!str_starts_with($attribute->get_name(), 'PHPUnit\Framework\Attributes\\')) {
                continue;
            }
            if (!class_exists($attribute->get_name())) {
                continue;
            }
            try {
                $attribute_instance = $attribute->new_instance();
            } catch (Error $e) {
                throw new Invalid_Attribute_Exception($attribute->get_name(), 'method ' . $class_name . '::' . $method_name . '()', $reflector->get_file_name(), $reflector->get_start_line(), $e->get_message());
            }
            switch ($attribute->get_name()) {
                case After::class:
                    assert($attribute_instance instanceof After);
                    $result[] = Metadata::after($attribute_instance->priority());
                    break;
                case After_Class::class:
                    assert($attribute_instance instanceof After_Class);
                    $result[] = Metadata::after_class($attribute_instance->priority());
                    break;
                case Allow_Mock_Objects_Without_Expectations::class:
                    assert($attribute_instance instanceof Allow_Mock_Objects_Without_Expectations);
                    $result[] = Metadata::allow_mock_objects_without_expectations_on_method();
                    break;
                case Backup_Globals::class:
                    assert($attribute_instance instanceof Backup_Globals);
                    $result[] = Metadata::backup_globals_on_method($attribute_instance->enabled());
                    break;
                case Backup_Static_Properties::class:
                    assert($attribute_instance instanceof Backup_Static_Properties);
                    $result[] = Metadata::backup_static_properties_on_method($attribute_instance->enabled());
                    break;
                case Before::class:
                    assert($attribute_instance instanceof Before);
                    $result[] = Metadata::before($attribute_instance->priority());
                    break;
                case Before_Class::class:
                    assert($attribute_instance instanceof Before_Class);
                    $result[] = Metadata::before_class($attribute_instance->priority());
                    break;
                case Covers_Nothing::class:
                    $result[] = Metadata::covers_nothing_on_method();
                    break;
                case Data_Provider::class:
                    assert($attribute_instance instanceof Data_Provider);
                    $result[] = Metadata::data_provider($class_name, $attribute_instance->method_name(), $attribute_instance->validate_argument_count());
                    break;
                case Data_Provider_External::class:
                    assert($attribute_instance instanceof Data_Provider_External);
                    $result[] = Metadata::data_provider($attribute_instance->class_name(), $attribute_instance->method_name(), $attribute_instance->validate_argument_count());
                    break;
                case Data_Provider_Closure::class:
                    assert($attribute_instance instanceof Data_Provider_Closure);
                    $result[] = Metadata::data_provider_closure($attribute_instance->closure(), $attribute_instance->validate_argument_count());
                    break;
                case Depends::class:
                    assert($attribute_instance instanceof Depends);
                    $result[] = Metadata::depends_on_method($class_name, $attribute_instance->method_name(), false, false);
                    break;
                case Depends_Using_Deep_Clone::class:
                    assert($attribute_instance instanceof Depends_Using_Deep_Clone);
                    $result[] = Metadata::depends_on_method($class_name, $attribute_instance->method_name(), true, false);
                    break;
                case Depends_Using_Shallow_Clone::class:
                    assert($attribute_instance instanceof Depends_Using_Shallow_Clone);
                    $result[] = Metadata::depends_on_method($class_name, $attribute_instance->method_name(), false, true);
                    break;
                case Depends_External::class:
                    assert($attribute_instance instanceof Depends_External);
                    $result[] = Metadata::depends_on_method($attribute_instance->class_name(), $attribute_instance->method_name(), false, false);
                    break;
                case Depends_External_Using_Deep_Clone::class:
                    assert($attribute_instance instanceof Depends_External_Using_Deep_Clone);
                    $result[] = Metadata::depends_on_method($attribute_instance->class_name(), $attribute_instance->method_name(), true, false);
                    break;
                case Depends_External_Using_Shallow_Clone::class:
                    assert($attribute_instance instanceof Depends_External_Using_Shallow_Clone);
                    $result[] = Metadata::depends_on_method($attribute_instance->class_name(), $attribute_instance->method_name(), false, true);
                    break;
                case Depends_On_Class::class:
                    assert($attribute_instance instanceof Depends_On_Class);
                    $result[] = Metadata::depends_on_class($attribute_instance->class_name(), false, false);
                    break;
                case Depends_On_Class_Using_Deep_Clone::class:
                    assert($attribute_instance instanceof Depends_On_Class_Using_Deep_Clone);
                    $result[] = Metadata::depends_on_class($attribute_instance->class_name(), true, false);
                    break;
                case Depends_On_Class_Using_Shallow_Clone::class:
                    assert($attribute_instance instanceof Depends_On_Class_Using_Shallow_Clone);
                    $result[] = Metadata::depends_on_class($attribute_instance->class_name(), false, true);
                    break;
                case Does_Not_Perform_Assertions::class:
                    assert($attribute_instance instanceof Does_Not_Perform_Assertions);
                    $result[] = Metadata::does_not_perform_assertions_on_method();
                    break;
                case Exclude_Global_Variable_From_Backup::class:
                    assert($attribute_instance instanceof Exclude_Global_Variable_From_Backup);
                    $result[] = Metadata::exclude_global_variable_from_backup_on_method($attribute_instance->global_variable_name());
                    break;
                case Exclude_Static_Property_From_Backup::class:
                    assert($attribute_instance instanceof Exclude_Static_Property_From_Backup);
                    $result[] = Metadata::exclude_static_property_from_backup_on_method($attribute_instance->class_name(), $attribute_instance->property_name());
                    break;
                case Group::class:
                    assert($attribute_instance instanceof Group);
                    if (!$this->is_size_group($attribute_instance->name(), $class_name, $method_name)) {
                        $result[] = Metadata::group_on_method($attribute_instance->name());
                    }
                    break;
                case Ignore_Deprecations::class:
                    assert($attribute_instance instanceof Ignore_Deprecations);
                    $result[] = Metadata::ignore_deprecations_on_method($attribute_instance->message_pattern());
                    break;
                case Ignore_Phpunit_Deprecations::class:
                    assert($attribute_instance instanceof Ignore_Phpunit_Deprecations);
                    $result[] = Metadata::ignore_phpunit_deprecations_on_method();
                    break;
                case Post_Condition::class:
                    assert($attribute_instance instanceof Post_Condition);
                    $result[] = Metadata::post_condition($attribute_instance->priority());
                    break;
                case Pre_Condition::class:
                    assert($attribute_instance instanceof Pre_Condition);
                    $result[] = Metadata::pre_condition($attribute_instance->priority());
                    break;
                case Preserve_Global_State::class:
                    assert($attribute_instance instanceof Preserve_Global_State);
                    $result[] = Metadata::preserve_global_state_on_method($attribute_instance->enabled());
                    break;
                case Requires_Method::class:
                    assert($attribute_instance instanceof Requires_Method);
                    $result[] = Metadata::requires_method_on_method($attribute_instance->class_name(), $attribute_instance->method_name());
                    break;
                case Requires_Function::class:
                    assert($attribute_instance instanceof Requires_Function);
                    $result[] = Metadata::requires_function_on_method($attribute_instance->function_name());
                    break;
                case Requires_Operating_System::class:
                    assert($attribute_instance instanceof Requires_Operating_System);
                    $result[] = Metadata::requires_operating_system_on_method($attribute_instance->regular_expression());
                    break;
                case Requires_Operating_System_Family::class:
                    assert($attribute_instance instanceof Requires_Operating_System_Family);
                    $result[] = Metadata::requires_operating_system_family_on_method($attribute_instance->operating_system_family());
                    break;
                case Requires_Php::class:
                    assert($attribute_instance instanceof Requires_Php);
                    $requirement = $this->requirement($attribute_instance->version_requirement(), $class_name, $method_name);
                    if ($requirement !== null) {
                        $result[] = Metadata::requires_php_on_method($requirement);
                    }
                    break;
                case Requires_Php_Extension::class:
                    assert($attribute_instance instanceof Requires_Php_Extension);
                    $version_constraint = null;
                    $version_requirement = $attribute_instance->version_requirement();
                    if ($version_requirement !== null) {
                        $version_constraint = $this->requirement($version_requirement, $class_name, $method_name);
                    }
                    $result[] = Metadata::requires_php_extension_on_method($attribute_instance->extension(), $version_constraint);
                    break;
                case Requires_Phpunit::class:
                    assert($attribute_instance instanceof Requires_Phpunit);
                    $requirement = $this->requirement($attribute_instance->version_requirement(), $class_name, $method_name);
                    if ($requirement !== null) {
                        $result[] = Metadata::requires_phpunit_on_method($requirement);
                    }
                    break;
                case Requires_Phpunit_Extension::class:
                    assert($attribute_instance instanceof Requires_Phpunit_Extension);
                    $result[] = Metadata::requires_phpunit_extension_on_method($attribute_instance->extension_class());
                    break;
                case Requires_Environment_Variable::class:
                    assert($attribute_instance instanceof Requires_Environment_Variable);
                    $result[] = Metadata::requires_environment_variable_on_method($attribute_instance->environment_variable_name(), $attribute_instance->value());
                    break;
                case With_Environment_Variable::class:
                    assert($attribute_instance instanceof With_Environment_Variable);
                    $result[] = Metadata::with_environment_variable_on_method($attribute_instance->environment_variable_name(), $attribute_instance->value());
                    break;
                case Requires_Setting::class:
                    assert($attribute_instance instanceof Requires_Setting);
                    $result[] = Metadata::requires_setting_on_method($attribute_instance->setting(), $attribute_instance->value());
                    break;
                case Run_In_Separate_Process::class:
                    $result[] = Metadata::run_in_separate_process();
                    break;
                case Test::class:
                    $result[] = Metadata::test();
                    break;
                case Test_Dox::class:
                    assert($attribute_instance instanceof Test_Dox);
                    $result[] = Metadata::test_dox_on_method($attribute_instance->text());
                    break;
                case Test_Dox_Formatter::class:
                    assert($attribute_instance instanceof Test_Dox_Formatter);
                    $result[] = Metadata::test_dox_formatter($class_name, $attribute_instance->method_name());
                    break;
                case Test_Dox_Formatter_External::class:
                    assert($attribute_instance instanceof Test_Dox_Formatter_External);
                    $result[] = Metadata::test_dox_formatter($attribute_instance->class_name(), $attribute_instance->method_name());
                    break;
                case Test_With::class:
                    assert($attribute_instance instanceof Test_With);
                    $result[] = Metadata::test_with($attribute_instance->data(), $attribute_instance->name());
                    break;
                case Test_With_Json::class:
                    assert($attribute_instance instanceof Test_With_Json);
                    $result[] = Metadata::test_with(json_decode($attribute_instance->json(), true, 512, JSON_THROW_ON_ERROR), $attribute_instance->name());
                    break;
                case Ticket::class:
                    assert($attribute_instance instanceof Ticket);
                    $result[] = Metadata::group_on_method($attribute_instance->text());
                    break;
                case Without_Error_Handler::class:
                    assert($attribute_instance instanceof Without_Error_Handler);
                    $result[] = Metadata::without_error_handler();
                    break;
                case Ignore_Phpunit_Warnings::class:
                    assert($attribute_instance instanceof Ignore_Phpunit_Warnings);
                    $result[] = Metadata::ignore_phpunit_warnings($attribute_instance->message_pattern());
                    break;
            }
        }
        return Metadata_Collection::from_array($result);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function for_class_and_method(string $class_name, string $method_name): Metadata_Collection
    {
        return $this->for_class($class_name)->merge_with($this->for_method($class_name, $method_name));
    }
    /**
     * @param non-empty-string  $groupName
     * @param class-string      $testClassName
     * @param ?non-empty-string $testMethodName
     */
    private function is_size_group(string $group_name, string $test_class_name, ?string $test_method_name = null): bool
    {
        $_group_name = strtolower(trim($group_name));
        if ($_group_name !== 'small' && $_group_name !== 'medium' && $_group_name !== 'large') {
            return false;
        }
        Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Group name "%s" is not allowed for %s', $_group_name, $this->test_as_string($test_class_name, $test_method_name)));
        return true;
    }
    /**
     * @param non-empty-string  $versionRequirement
     * @param class-string      $testClassName
     * @param ?non-empty-string $testMethodName
     */
    private function requirement(string $version_requirement, string $test_class_name, ?string $test_method_name = null): ?Requirement
    {
        if (is_numeric(trim($version_requirement))) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Test %s has attribute with version constraint string argument without explicit version comparison operator ("%s"), version constraint is ignored', $this->test_as_string($test_class_name, $test_method_name), $version_requirement));
            return null;
        }
        return Requirement::from($version_requirement);
    }
    /**
     * @param class-string      $testClassName
     * @param ?non-empty-string $testMethodName
     *
     * @return non-empty-string
     */
    private function test_as_string(string $test_class_name, ?string $test_method_name = null): string
    {
        return sprintf('%s %s%s%s', $test_method_name !== null ? 'method' : 'class', $test_class_name, $test_method_name !== null ? '::' : '', $test_method_name ?? '');
    }
}