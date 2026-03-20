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
namespace Php_Unit\Metadata;

use Closure;
use Php_Unit\Metadata\Version\Requirement;
use Php_Unit\Runner\Extension\Extension;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Metadata
{
    public static function after(int $priority): After
    {
        return new After(Level::METHOD_LEVEL, $priority);
    }
    public static function after_class(int $priority): After_Class
    {
        return new After_Class(Level::METHOD_LEVEL, $priority);
    }
    public static function allow_mock_objects_without_expectations_on_class(): Allow_Mock_Objects_Without_Expectations
    {
        return new Allow_Mock_Objects_Without_Expectations(Level::CLASS_LEVEL);
    }
    public static function allow_mock_objects_without_expectations_on_method(): Allow_Mock_Objects_Without_Expectations
    {
        return new Allow_Mock_Objects_Without_Expectations(Level::METHOD_LEVEL);
    }
    public static function backup_globals_on_class(bool $enabled): Backup_Globals
    {
        return new Backup_Globals(Level::CLASS_LEVEL, $enabled);
    }
    public static function backup_globals_on_method(bool $enabled): Backup_Globals
    {
        return new Backup_Globals(Level::METHOD_LEVEL, $enabled);
    }
    public static function backup_static_properties_on_class(bool $enabled): Backup_Static_Properties
    {
        return new Backup_Static_Properties(Level::CLASS_LEVEL, $enabled);
    }
    public static function backup_static_properties_on_method(bool $enabled): Backup_Static_Properties
    {
        return new Backup_Static_Properties(Level::METHOD_LEVEL, $enabled);
    }
    public static function before(int $priority): Before
    {
        return new Before(Level::METHOD_LEVEL, $priority);
    }
    public static function before_class(int $priority): Before_Class
    {
        return new Before_Class(Level::METHOD_LEVEL, $priority);
    }
    /**
     * @param non-empty-string $namespace
     */
    public static function covers_namespace(string $namespace): Covers_Namespace
    {
        return new Covers_Namespace(Level::CLASS_LEVEL, $namespace);
    }
    /**
     * @param class-string $className
     */
    public static function covers_class(string $class_name): Covers_Class
    {
        return new Covers_Class(Level::CLASS_LEVEL, $class_name);
    }
    /**
     * @param class-string $className
     */
    public static function covers_classes_that_extend_class(string $class_name): Covers_Classes_That_Extend_Class
    {
        return new Covers_Classes_That_Extend_Class(Level::CLASS_LEVEL, $class_name);
    }
    /**
     * @param class-string $interfaceName
     */
    public static function covers_classes_that_implement_interface(string $interface_name): Covers_Classes_That_Implement_Interface
    {
        return new Covers_Classes_That_Implement_Interface(Level::CLASS_LEVEL, $interface_name);
    }
    /**
     * @param trait-string $traitName
     */
    public static function covers_trait(string $trait_name): Covers_Trait
    {
        return new Covers_Trait(Level::CLASS_LEVEL, $trait_name);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function covers_method(string $class_name, string $method_name): Covers_Method
    {
        return new Covers_Method(Level::CLASS_LEVEL, $class_name, $method_name);
    }
    /**
     * @param non-empty-string $functionName
     */
    public static function covers_function(string $function_name): Covers_Function
    {
        return new Covers_Function(Level::CLASS_LEVEL, $function_name);
    }
    public static function covers_nothing_on_class(): Covers_Nothing
    {
        return new Covers_Nothing(Level::CLASS_LEVEL);
    }
    public static function covers_nothing_on_method(): Covers_Nothing
    {
        return new Covers_Nothing(Level::METHOD_LEVEL);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function data_provider(string $class_name, string $method_name, bool $validate_argument_count): Data_Provider
    {
        return new Data_Provider(Level::METHOD_LEVEL, $class_name, $method_name, $validate_argument_count);
    }
    public static function data_provider_closure(Closure $callable, bool $validate_argument_count): Data_Provider_Closure
    {
        return new Data_Provider_Closure(Level::METHOD_LEVEL, $callable, $validate_argument_count);
    }
    /**
     * @param class-string $className
     */
    public static function depends_on_class(string $class_name, bool $deep_clone, bool $shallow_clone): Depends_On_Class
    {
        return new Depends_On_Class(Level::METHOD_LEVEL, $class_name, $deep_clone, $shallow_clone);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function depends_on_method(string $class_name, string $method_name, bool $deep_clone, bool $shallow_clone): Depends_On_Method
    {
        return new Depends_On_Method(Level::METHOD_LEVEL, $class_name, $method_name, $deep_clone, $shallow_clone);
    }
    public static function disable_return_value_generation_for_test_doubles(): Disable_Return_Value_Generation_For_Test_Doubles
    {
        return new Disable_Return_Value_Generation_For_Test_Doubles(Level::CLASS_LEVEL);
    }
    public static function does_not_perform_assertions_on_class(): Does_Not_Perform_Assertions
    {
        return new Does_Not_Perform_Assertions(Level::CLASS_LEVEL);
    }
    public static function does_not_perform_assertions_on_method(): Does_Not_Perform_Assertions
    {
        return new Does_Not_Perform_Assertions(Level::METHOD_LEVEL);
    }
    /**
     * @param non-empty-string $globalVariableName
     */
    public static function exclude_global_variable_from_backup_on_class(string $global_variable_name): Exclude_Global_Variable_From_Backup
    {
        return new Exclude_Global_Variable_From_Backup(Level::CLASS_LEVEL, $global_variable_name);
    }
    /**
     * @param non-empty-string $globalVariableName
     */
    public static function exclude_global_variable_from_backup_on_method(string $global_variable_name): Exclude_Global_Variable_From_Backup
    {
        return new Exclude_Global_Variable_From_Backup(Level::METHOD_LEVEL, $global_variable_name);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $propertyName
     */
    public static function exclude_static_property_from_backup_on_class(string $class_name, string $property_name): Exclude_Static_Property_From_Backup
    {
        return new Exclude_Static_Property_From_Backup(Level::CLASS_LEVEL, $class_name, $property_name);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $propertyName
     */
    public static function exclude_static_property_from_backup_on_method(string $class_name, string $property_name): Exclude_Static_Property_From_Backup
    {
        return new Exclude_Static_Property_From_Backup(Level::METHOD_LEVEL, $class_name, $property_name);
    }
    /**
     * @param non-empty-string $groupName
     */
    public static function group_on_class(string $group_name): Group
    {
        return new Group(Level::CLASS_LEVEL, $group_name);
    }
    /**
     * @param non-empty-string $groupName
     */
    public static function group_on_method(string $group_name): Group
    {
        return new Group(Level::METHOD_LEVEL, $group_name);
    }
    /**
     * @param null|non-empty-string $messagePattern
     */
    public static function ignore_deprecations_on_class(?string $message_pattern = null): Ignore_Deprecations
    {
        return new Ignore_Deprecations(Level::CLASS_LEVEL, $message_pattern);
    }
    /**
     * @param null|non-empty-string $messagePattern
     */
    public static function ignore_deprecations_on_method(?string $message_pattern = null): Ignore_Deprecations
    {
        return new Ignore_Deprecations(Level::METHOD_LEVEL, $message_pattern);
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function ignore_phpunit_deprecations_on_class(): Ignore_Phpunit_Deprecations
    {
        return new Ignore_Phpunit_Deprecations(Level::CLASS_LEVEL);
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function ignore_phpunit_deprecations_on_method(): Ignore_Phpunit_Deprecations
    {
        return new Ignore_Phpunit_Deprecations(Level::METHOD_LEVEL);
    }
    public static function post_condition(int $priority): Post_Condition
    {
        return new Post_Condition(Level::METHOD_LEVEL, $priority);
    }
    public static function pre_condition(int $priority): Pre_Condition
    {
        return new Pre_Condition(Level::METHOD_LEVEL, $priority);
    }
    public static function preserve_global_state_on_class(bool $enabled): Preserve_Global_State
    {
        return new Preserve_Global_State(Level::CLASS_LEVEL, $enabled);
    }
    public static function preserve_global_state_on_method(bool $enabled): Preserve_Global_State
    {
        return new Preserve_Global_State(Level::METHOD_LEVEL, $enabled);
    }
    /**
     * @param non-empty-string $functionName
     */
    public static function requires_function_on_class(string $function_name): Requires_Function
    {
        return new Requires_Function(Level::CLASS_LEVEL, $function_name);
    }
    /**
     * @param non-empty-string $functionName
     */
    public static function requires_function_on_method(string $function_name): Requires_Function
    {
        return new Requires_Function(Level::METHOD_LEVEL, $function_name);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function requires_method_on_class(string $class_name, string $method_name): Requires_Method
    {
        return new Requires_Method(Level::CLASS_LEVEL, $class_name, $method_name);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function requires_method_on_method(string $class_name, string $method_name): Requires_Method
    {
        return new Requires_Method(Level::METHOD_LEVEL, $class_name, $method_name);
    }
    /**
     * @param non-empty-string $operatingSystem
     */
    public static function requires_operating_system_on_class(string $operating_system): Requires_Operating_System
    {
        return new Requires_Operating_System(Level::CLASS_LEVEL, $operating_system);
    }
    /**
     * @param non-empty-string $operatingSystem
     */
    public static function requires_operating_system_on_method(string $operating_system): Requires_Operating_System
    {
        return new Requires_Operating_System(Level::METHOD_LEVEL, $operating_system);
    }
    /**
     * @param non-empty-string $operatingSystemFamily
     */
    public static function requires_operating_system_family_on_class(string $operating_system_family): Requires_Operating_System_Family
    {
        return new Requires_Operating_System_Family(Level::CLASS_LEVEL, $operating_system_family);
    }
    /**
     * @param non-empty-string $operatingSystemFamily
     */
    public static function requires_operating_system_family_on_method(string $operating_system_family): Requires_Operating_System_Family
    {
        return new Requires_Operating_System_Family(Level::METHOD_LEVEL, $operating_system_family);
    }
    public static function requires_php_on_class(Requirement $version_requirement): Requires_Php
    {
        return new Requires_Php(Level::CLASS_LEVEL, $version_requirement);
    }
    public static function requires_php_on_method(Requirement $version_requirement): Requires_Php
    {
        return new Requires_Php(Level::METHOD_LEVEL, $version_requirement);
    }
    /**
     * @param non-empty-string $extension
     */
    public static function requires_php_extension_on_class(string $extension, ?Requirement $version_requirement): Requires_Php_Extension
    {
        return new Requires_Php_Extension(Level::CLASS_LEVEL, $extension, $version_requirement);
    }
    /**
     * @param non-empty-string $extension
     */
    public static function requires_php_extension_on_method(string $extension, ?Requirement $version_requirement): Requires_Php_Extension
    {
        return new Requires_Php_Extension(Level::METHOD_LEVEL, $extension, $version_requirement);
    }
    public static function requires_phpunit_on_class(Requirement $version_requirement): Requires_Phpunit
    {
        return new Requires_Phpunit(Level::CLASS_LEVEL, $version_requirement);
    }
    public static function requires_phpunit_on_method(Requirement $version_requirement): Requires_Phpunit
    {
        return new Requires_Phpunit(Level::METHOD_LEVEL, $version_requirement);
    }
    /**
     * @param class-string<Extension> $extensionClass
     */
    public static function requires_phpunit_extension_on_class(string $extension_class): Requires_Phpunit_Extension
    {
        return new Requires_Phpunit_Extension(Level::CLASS_LEVEL, $extension_class);
    }
    /**
     * @param class-string<Extension> $extensionClass
     */
    public static function requires_phpunit_extension_on_method(string $extension_class): Requires_Phpunit_Extension
    {
        return new Requires_Phpunit_Extension(Level::METHOD_LEVEL, $extension_class);
    }
    public static function requires_environment_variable_on_class(string $environment_variable_name, null|string $value): Requires_Environment_Variable
    {
        return new Requires_Environment_Variable(Level::CLASS_LEVEL, $environment_variable_name, $value);
    }
    public static function requires_environment_variable_on_method(string $environment_variable_name, null|string $value): Requires_Environment_Variable
    {
        return new Requires_Environment_Variable(Level::METHOD_LEVEL, $environment_variable_name, $value);
    }
    public static function with_environment_variable_on_class(string $environment_variable_name, null|string $value): With_Environment_Variable
    {
        return new With_Environment_Variable(Level::CLASS_LEVEL, $environment_variable_name, $value);
    }
    public static function with_environment_variable_on_method(string $environment_variable_name, null|string $value): With_Environment_Variable
    {
        return new With_Environment_Variable(Level::METHOD_LEVEL, $environment_variable_name, $value);
    }
    /**
     * @param non-empty-string $setting
     * @param non-empty-string $value
     */
    public static function requires_setting_on_class(string $setting, string $value): Requires_Setting
    {
        return new Requires_Setting(Level::CLASS_LEVEL, $setting, $value);
    }
    /**
     * @param non-empty-string $setting
     * @param non-empty-string $value
     */
    public static function requires_setting_on_method(string $setting, string $value): Requires_Setting
    {
        return new Requires_Setting(Level::METHOD_LEVEL, $setting, $value);
    }
    public static function run_tests_in_separate_processes(): Run_Tests_In_Separate_Processes
    {
        return new Run_Tests_In_Separate_Processes(Level::CLASS_LEVEL);
    }
    public static function run_in_separate_process(): Run_In_Separate_Process
    {
        return new Run_In_Separate_Process(Level::METHOD_LEVEL);
    }
    public static function test(): Test
    {
        return new Test(Level::METHOD_LEVEL);
    }
    /**
     * @param non-empty-string $text
     */
    public static function test_dox_on_class(string $text): Test_Dox
    {
        return new Test_Dox(Level::CLASS_LEVEL, $text);
    }
    /**
     * @param non-empty-string $text
     */
    public static function test_dox_on_method(string $text): Test_Dox
    {
        return new Test_Dox(Level::METHOD_LEVEL, $text);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function test_dox_formatter(string $class_name, string $method_name): Test_Dox_Formatter
    {
        return new Test_Dox_Formatter(Level::METHOD_LEVEL, $class_name, $method_name);
    }
    /**
     * @param ?non-empty-string $name
     */
    public static function test_with(mixed $data, ?string $name = null): Test_With
    {
        return new Test_With(Level::METHOD_LEVEL, $data, $name);
    }
    /**
     * @param non-empty-string $namespace
     */
    public static function uses_namespace(string $namespace): Uses_Namespace
    {
        return new Uses_Namespace(Level::CLASS_LEVEL, $namespace);
    }
    /**
     * @param class-string $className
     */
    public static function uses_class(string $class_name): Uses_Class
    {
        return new Uses_Class(Level::CLASS_LEVEL, $class_name);
    }
    /**
     * @param class-string $className
     */
    public static function uses_classes_that_extend_class(string $class_name): Uses_Classes_That_Extend_Class
    {
        return new Uses_Classes_That_Extend_Class(Level::CLASS_LEVEL, $class_name);
    }
    /**
     * @param class-string $interfaceName
     */
    public static function uses_classes_that_implement_interface(string $interface_name): Uses_Classes_That_Implement_Interface
    {
        return new Uses_Classes_That_Implement_Interface(Level::CLASS_LEVEL, $interface_name);
    }
    /**
     * @param trait-string $traitName
     */
    public static function uses_trait(string $trait_name): Uses_Trait
    {
        return new Uses_Trait(Level::CLASS_LEVEL, $trait_name);
    }
    /**
     * @param non-empty-string $functionName
     */
    public static function uses_function(string $function_name): Uses_Function
    {
        return new Uses_Function(Level::CLASS_LEVEL, $function_name);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function uses_method(string $class_name, string $method_name): Uses_Method
    {
        return new Uses_Method(Level::CLASS_LEVEL, $class_name, $method_name);
    }
    public static function without_error_handler(): Without_Error_Handler
    {
        return new Without_Error_Handler(Level::METHOD_LEVEL);
    }
    /**
     * @param null|non-empty-string $messagePattern
     */
    public static function ignore_phpunit_warnings(?string $message_pattern): Ignore_Phpunit_Warnings
    {
        return new Ignore_Phpunit_Warnings(Level::METHOD_LEVEL, $message_pattern);
    }
    protected function __construct(private Level $level)
    {
    }
    public function is_class_level(): bool
    {
        return $this->level === Level::CLASS_LEVEL;
    }
    public function is_method_level(): bool
    {
        return $this->level === Level::METHOD_LEVEL;
    }
    /**
     * @phpstan-assert-if-true After $this
     */
    public function is_after(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true AfterClass $this
     */
    public function is_after_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true AllowMockObjectsWithoutExpectations $this
     */
    public function is_allow_mock_objects_without_expectations(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true BackupGlobals $this
     */
    public function is_backup_globals(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true BackupStaticProperties $this
     */
    public function is_backup_static_properties(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true BeforeClass $this
     */
    public function is_before_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Before $this
     */
    public function is_before(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversNamespace $this
     */
    public function is_covers_namespace(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversClass $this
     */
    public function is_covers_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversClassesThatExtendClass $this
     */
    public function is_covers_classes_that_extend_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversClassesThatImplementInterface $this
     */
    public function is_covers_classes_that_implement_interface(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversTrait $this
     */
    public function is_covers_trait(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversFunction $this
     */
    public function is_covers_function(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversMethod $this
     */
    public function is_covers_method(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true CoversNothing $this
     */
    public function is_covers_nothing(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true DataProvider $this
     */
    public function is_data_provider(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true DataProviderClosure $this
     */
    public function is_data_provider_closure(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true DependsOnClass $this
     */
    public function is_depends_on_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true DependsOnMethod $this
     */
    public function is_depends_on_method(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true DisableReturnValueGenerationForTestDoubles $this
     */
    public function is_disable_return_value_generation_for_test_doubles(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true DoesNotPerformAssertions $this
     */
    public function is_does_not_perform_assertions(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true ExcludeGlobalVariableFromBackup $this
     */
    public function is_exclude_global_variable_from_backup(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true ExcludeStaticPropertyFromBackup $this
     */
    public function is_exclude_static_property_from_backup(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Group $this
     */
    public function is_group(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true IgnoreDeprecations $this
     */
    public function is_ignore_deprecations(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true IgnorePhpunitDeprecations $this
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function is_ignore_phpunit_deprecations(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RunInSeparateProcess $this
     */
    public function is_run_in_separate_process(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RunTestsInSeparateProcesses $this
     */
    public function is_run_tests_in_separate_processes(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Test $this
     */
    public function is_test(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true PreCondition $this
     */
    public function is_pre_condition(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true PostCondition $this
     */
    public function is_post_condition(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true PreserveGlobalState $this
     */
    public function is_preserve_global_state(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresMethod $this
     */
    public function is_requires_method(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresFunction $this
     */
    public function is_requires_function(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresOperatingSystem $this
     */
    public function is_requires_operating_system(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresOperatingSystemFamily $this
     */
    public function is_requires_operating_system_family(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresPhp $this
     */
    public function is_requires_php(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresPhpExtension $this
     */
    public function is_requires_php_extension(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresPhpunit $this
     */
    public function is_requires_phpunit(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresPhpunitExtension $this
     */
    public function is_requires_phpunit_extension(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresEnvironmentVariable $this
     */
    public function is_requires_environment_variable(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true WithEnvironmentVariable $this
     */
    public function is_with_environment_variable(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true RequiresSetting $this
     */
    public function is_requires_setting(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true TestDox $this
     */
    public function is_test_dox(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true TestDoxFormatter $this
     */
    public function is_test_dox_formatter(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true TestWith $this
     */
    public function is_test_with(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesNamespace $this
     */
    public function is_uses_namespace(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesClass $this
     */
    public function is_uses_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesClassesThatExtendClass $this
     */
    public function is_uses_classes_that_extend_class(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesClassesThatImplementInterface $this
     */
    public function is_uses_classes_that_implement_interface(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesTrait $this
     */
    public function is_uses_trait(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesFunction $this
     */
    public function is_uses_function(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true UsesMethod $this
     */
    public function is_uses_method(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true WithoutErrorHandler $this
     */
    public function is_without_error_handler(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true IgnorePhpunitWarnings $this
     */
    public function is_ignore_phpunit_warnings(): bool
    {
        return false;
    }
}