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
namespace Php_Unit\Framework;

use function array_merge;
use function assert;
use Php_Unit\Metadata\Api\Data_Provider;
use Php_Unit\Metadata\Api\Groups;
use Php_Unit\Metadata\Api\Provided_Data;
use Php_Unit\Metadata\Api\Requirements;
use Php_Unit\Metadata\Backup_Globals;
use Php_Unit\Metadata\Backup_Static_Properties;
use Php_Unit\Metadata\Exclude_Global_Variable_From_Backup;
use Php_Unit\Metadata\Exclude_Static_Property_From_Backup;
use Php_Unit\Metadata\Parser\Registry as MetadataRegistry;
use Php_Unit\Metadata\Preserve_Global_State;
use Php_Unit\Runner\Error_Handler;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use ReflectionClass;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Builder
{
    /**
     * @param ReflectionClass<TestCase> $theClass
     * @param non-empty-string          $methodName
     * @param list<non-empty-string>    $groups
     *
     * @throws InvalidDataProviderException
     */
    public function build(ReflectionClass $the_class, string $method_name, array $groups = []): Test
    {
        $class_name = $the_class->get_name();
        $data = null;
        if ($this->requirements_satisfied($class_name, $method_name)) {
            try {
                Error_Handler::instance()->enter_test_case_context($class_name, $method_name);
                $data = (new Data_Provider())->provided_data($class_name, $method_name);
            } finally {
                Error_Handler::instance()->leave_test_case_context();
            }
        }
        if ($data !== null) {
            return $this->build_data_provider_test_suite($method_name, $class_name, $data, $this->should_test_method_be_run_in_separate_process($class_name, $method_name), $this->should_global_state_be_preserved($class_name, $method_name), $this->backup_settings($class_name, $method_name), $groups);
        }
        $test = new $class_name($method_name);
        $this->configure_test_case($test, $this->should_test_method_be_run_in_separate_process($class_name, $method_name), $this->should_global_state_be_preserved($class_name, $method_name), $this->backup_settings($class_name, $method_name));
        return $test;
    }
    /**
     * @param non-empty-string                                                                                                                                                  $methodName
     * @param class-string<TestCase>                                                                                                                                            $className
     * @param array<ProvidedData>                                                                                                                                               $data
     * @param array{backupGlobals: ?true, backupGlobalsExcludeList: list<string>, backupStaticProperties: ?true, backupStaticPropertiesExcludeList: array<string,list<string>>} $backupSettings
     * @param list<non-empty-string>                                                                                                                                            $groups
     */
    private function build_data_provider_test_suite(string $method_name, string $class_name, array $data, bool $run_test_in_separate_process, ?bool $preserve_global_state, array $backup_settings, array $groups): Data_Provider_Test_Suite
    {
        $data_provider_test_suite = Data_Provider_Test_Suite::empty($class_name . '::' . $method_name);
        $groups = array_merge($groups, (new Groups())->groups($class_name, $method_name));
        foreach ($data as $_data_name => $_data) {
            $_test = new $class_name($method_name);
            $_test->set_data($_data_name, $_data->value());
            $this->configure_test_case($_test, $run_test_in_separate_process, $preserve_global_state, $backup_settings);
            $data_provider_test_suite->add_test($_test, $groups);
        }
        return $data_provider_test_suite;
    }
    /**
     * @param array{backupGlobals: ?true, backupGlobalsExcludeList: list<string>, backupStaticProperties: ?true, backupStaticPropertiesExcludeList: array<string,list<string>>} $backupSettings
     */
    private function configure_test_case(Test_Case $test, bool $run_test_in_separate_process, ?bool $preserve_global_state, array $backup_settings): void
    {
        if ($run_test_in_separate_process) {
            $test->set_run_test_in_separate_process(true);
        }
        if ($preserve_global_state !== null) {
            $test->set_preserve_global_state($preserve_global_state);
        }
        if ($backup_settings['backupGlobals'] !== null) {
            $test->set_backup_globals($backup_settings['backupGlobals']);
        } else {
            $test->set_backup_globals(Configuration_Registry::get()->backup_globals());
        }
        $test->set_backup_globals_exclude_list($backup_settings['backupGlobalsExcludeList']);
        if ($backup_settings['backupStaticProperties'] !== null) {
            $test->set_backup_static_properties($backup_settings['backupStaticProperties']);
        } else {
            $test->set_backup_static_properties(Configuration_Registry::get()->backup_static_properties());
        }
        $test->set_backup_static_properties_exclude_list($backup_settings['backupStaticPropertiesExcludeList']);
    }
    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @return array{backupGlobals: ?true, backupGlobalsExcludeList: list<string>, backupStaticProperties: ?true, backupStaticPropertiesExcludeList: array<string,list<string>>}
     */
    private function backup_settings(string $class_name, string $method_name): array
    {
        $metadata_for_class = Metadata_Registry::parser()->for_class($class_name);
        $metadata_for_method = Metadata_Registry::parser()->for_method($class_name, $method_name);
        $metadata_for_class_and_method = Metadata_Registry::parser()->for_class_and_method($class_name, $method_name);
        $backup_globals = null;
        $backup_globals_exclude_list = [];
        if ($metadata_for_method->is_backup_globals()->is_not_empty()) {
            $metadata = $metadata_for_method->is_backup_globals()->as_array()[0];
            assert($metadata instanceof Backup_Globals);
            if ($metadata->enabled()) {
                $backup_globals = true;
            }
        } elseif ($metadata_for_class->is_backup_globals()->is_not_empty()) {
            $metadata = $metadata_for_class->is_backup_globals()->as_array()[0];
            assert($metadata instanceof Backup_Globals);
            if ($metadata->enabled()) {
                $backup_globals = true;
            }
        }
        foreach ($metadata_for_class_and_method->is_exclude_global_variable_from_backup() as $metadata) {
            assert($metadata instanceof Exclude_Global_Variable_From_Backup);
            $backup_globals_exclude_list[] = $metadata->global_variable_name();
        }
        $backup_static_properties = null;
        $backup_static_properties_exclude_list = [];
        if ($metadata_for_method->is_backup_static_properties()->is_not_empty()) {
            $metadata = $metadata_for_method->is_backup_static_properties()->as_array()[0];
            assert($metadata instanceof Backup_Static_Properties);
            if ($metadata->enabled()) {
                $backup_static_properties = true;
            }
        } elseif ($metadata_for_class->is_backup_static_properties()->is_not_empty()) {
            $metadata = $metadata_for_class->is_backup_static_properties()->as_array()[0];
            assert($metadata instanceof Backup_Static_Properties);
            if ($metadata->enabled()) {
                $backup_static_properties = true;
            }
        }
        foreach ($metadata_for_class_and_method->is_exclude_static_property_from_backup() as $metadata) {
            assert($metadata instanceof Exclude_Static_Property_From_Backup);
            if (!isset($backup_static_properties_exclude_list[$metadata->class_name()])) {
                $backup_static_properties_exclude_list[$metadata->class_name()] = [];
            }
            $backup_static_properties_exclude_list[$metadata->class_name()][] = $metadata->property_name();
        }
        return ['backupGlobals' => $backup_globals, 'backupGlobalsExcludeList' => $backup_globals_exclude_list, 'backupStaticProperties' => $backup_static_properties, 'backupStaticPropertiesExcludeList' => $backup_static_properties_exclude_list];
    }
    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    private function should_global_state_be_preserved(string $class_name, string $method_name): ?bool
    {
        $metadata_for_method = Metadata_Registry::parser()->for_method($class_name, $method_name);
        if ($metadata_for_method->is_preserve_global_state()->is_not_empty()) {
            $metadata = $metadata_for_method->is_preserve_global_state()->as_array()[0];
            assert($metadata instanceof Preserve_Global_State);
            return $metadata->enabled();
        }
        $metadata_for_class = Metadata_Registry::parser()->for_class($class_name);
        if ($metadata_for_class->is_preserve_global_state()->is_not_empty()) {
            $metadata = $metadata_for_class->is_preserve_global_state()->as_array()[0];
            assert($metadata instanceof Preserve_Global_State);
            return $metadata->enabled();
        }
        return null;
    }
    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    private function should_test_method_be_run_in_separate_process(string $class_name, string $method_name): bool
    {
        if (Metadata_Registry::parser()->for_class($class_name)->is_run_tests_in_separate_processes()->is_not_empty()) {
            return true;
        }
        if (Metadata_Registry::parser()->for_method($class_name, $method_name)->is_run_in_separate_process()->is_not_empty()) {
            return true;
        }
        return false;
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    private function requirements_satisfied(string $class_name, string $method_name): bool
    {
        return (new Requirements())->requirements_not_satisfied_for($class_name, $method_name) === [];
    }
}