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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use function assert;
use function defined;
use const DIRECTORY_SEPARATOR;
use function dirname;
use Dom_Document;
use Dom_Element;
use Dom_Node;
use Dom_Node_List;
use Domx_Path;
use function explode;
use function is_numeric;
use const PHP_EOL;
use const PHP_VERSION;
use Php_Unit\Runner\Test_Suite_Sorter;
use Php_Unit\Runner\Version;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Configuration\Constant;
use Php_Unit\Text_Ui\Configuration\Constant_Collection;
use Php_Unit\Text_Ui\Configuration\Directory;
use Php_Unit\Text_Ui\Configuration\Directory_Collection;
use Php_Unit\Text_Ui\Configuration\Extension_Bootstrap;
use Php_Unit\Text_Ui\Configuration\Extension_Bootstrap_Collection;
use Php_Unit\Text_Ui\Configuration\File;
use Php_Unit\Text_Ui\Configuration\File_Collection;
use Php_Unit\Text_Ui\Configuration\Filter_Directory;
use Php_Unit\Text_Ui\Configuration\Filter_Directory_Collection;
use Php_Unit\Text_Ui\Configuration\Filter_File;
use Php_Unit\Text_Ui\Configuration\Filter_File_Collection;
use Php_Unit\Text_Ui\Configuration\Group;
use Php_Unit\Text_Ui\Configuration\Group_Collection;
use Php_Unit\Text_Ui\Configuration\Ini_Setting;
use Php_Unit\Text_Ui\Configuration\Ini_Setting_Collection;
use Php_Unit\Text_Ui\Configuration\Php;
use Php_Unit\Text_Ui\Configuration\Source;
use Php_Unit\Text_Ui\Configuration\Test_Directory;
use Php_Unit\Text_Ui\Configuration\Test_Directory_Collection;
use Php_Unit\Text_Ui\Configuration\Test_File;
use Php_Unit\Text_Ui\Configuration\Test_File_Collection;
use Php_Unit\Text_Ui\Configuration\Test_Suite as TestSuiteConfiguration;
use Php_Unit\Text_Ui\Configuration\Test_Suite_Collection;
use Php_Unit\Text_Ui\Configuration\Variable;
use Php_Unit\Text_Ui\Configuration\Variable_Collection;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Code_Coverage;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Clover;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Cobertura;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Crap4j;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Html as CodeCoverageHtml;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Open_Clover;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Php as CodeCoveragePhp;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Text as CodeCoverageText;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Xml as CodeCoverageXml;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Junit;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Logging;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Otr;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Team_City;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Test_Dox\Html as TestDoxHtml;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Test_Dox\Text as TestDoxText;
use Php_Unit\Util\Version_Comparison_Operator;
use Php_Unit\Util\Xml\Loader as XmlLoader;
use Php_Unit\Util\Xml\Xml_Exception;
use function preg_match;
use function realpath;
use Sebastian_Bergmann\Code_Coverage\Report\Html\Colors;
use Sebastian_Bergmann\Code_Coverage\Report\Thresholds;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;
use Throwable;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Loader
{
    /**
     * @throws Exception
     */
    public function load(string $filename): Loaded_From_File_Configuration
    {
        try {
            $document = (new Xml_Loader())->load_file($filename);
        } catch (Xml_Exception $e) {
            throw new Exception($e->get_message(), $e->get_code(), $e);
        }
        $xpath = new Domx_Path($document);
        try {
            $xsd_filename = (new Schema_Finder())->find(Version::series());
        } catch (Cannot_Find_Schema_Exception $e) {
            throw new Exception($e->get_message(), $e->get_code(), $e);
        }
        $configuration_file_realpath = realpath($filename);
        assert($configuration_file_realpath !== false && $configuration_file_realpath !== '');
        $validation_result = (new Validator())->validate($document, $xsd_filename);
        try {
            return new Loaded_From_File_Configuration($configuration_file_realpath, $validation_result, $this->extensions($xpath), $this->source($configuration_file_realpath, $xpath), $this->code_coverage($configuration_file_realpath, $xpath), $this->groups($xpath), $this->logging($configuration_file_realpath, $xpath), $this->php($configuration_file_realpath, $xpath), $this->phpunit($configuration_file_realpath, $document, $xpath), $this->test_suite($configuration_file_realpath, $xpath));
        } catch (Throwable $t) {
            $message = sprintf('Cannot load XML configuration file %s', $configuration_file_realpath);
            if ($validation_result->has_validation_errors()) {
                $message .= ' because it has validation errors:' . PHP_EOL . $validation_result->as_string();
            }
            throw new Exception($message, previous: $t);
        }
    }
    private function logging(string $filename, Domx_Path $xpath): Logging
    {
        $junit = null;
        $element = $this->element($xpath, 'logging/junit');
        if ($element !== null) {
            $junit = new Junit(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $otr = null;
        $element = $this->element($xpath, 'logging/otr');
        if ($element !== null) {
            $otr = new Otr(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))), $this->parse_boolean_attribute($element, 'includeGitInformation', false));
        }
        $team_city = null;
        $element = $this->element($xpath, 'logging/teamcity');
        if ($element !== null) {
            $team_city = new Team_City(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $test_dox_html = null;
        $element = $this->element($xpath, 'logging/testdoxHtml');
        if ($element !== null) {
            $test_dox_html = new Test_Dox_Html(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $test_dox_text = null;
        $element = $this->element($xpath, 'logging/testdoxText');
        if ($element !== null) {
            $test_dox_text = new Test_Dox_Text(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        return new Logging($junit, $otr, $team_city, $test_dox_html, $test_dox_text);
    }
    private function extensions(Domx_Path $xpath): Extension_Bootstrap_Collection
    {
        $extension_bootstrappers = [];
        $bootstrap_nodes = $xpath->query('extensions/bootstrap');
        assert($bootstrap_nodes instanceof Dom_Node_List);
        foreach ($bootstrap_nodes as $bootstrap) {
            assert($bootstrap instanceof Dom_Element);
            $parameters = [];
            $parameter_nodes = $xpath->query('parameter', $bootstrap);
            assert($parameter_nodes instanceof Dom_Node_List);
            foreach ($parameter_nodes as $parameter) {
                assert($parameter instanceof Dom_Element);
                $parameters[$parameter->get_attribute('name')] = $parameter->get_attribute('value');
            }
            $class_name = $bootstrap->get_attribute('class');
            assert($class_name !== '');
            $extension_bootstrappers[] = new Extension_Bootstrap($class_name, $parameters);
        }
        return Extension_Bootstrap_Collection::from_array($extension_bootstrappers);
    }
    /**
     * @return non-empty-string
     */
    private function to_absolute_path(string $filename, string $path): string
    {
        $path = trim($path);
        if (str_starts_with($path, '/')) {
            return $path;
        }
        // Matches the following on Windows:
        //  - \\NetworkComputer\Path
        //  - \\.\D:
        //  - \\.\c:
        //  - C:\Windows
        //  - C:\windows
        //  - C:/windows
        //  - c:/windows
        if (defined('PHP_WINDOWS_VERSION_BUILD') && $path !== '' && ($path[0] === '\\' || strlen($path) >= 3 && preg_match('#^[A-Z]:[/\\\\]#i', substr($path, 0, 3)))) {
            return $path;
        }
        if (str_contains($path, '://')) {
            return $path;
        }
        return dirname($filename) . DIRECTORY_SEPARATOR . $path;
    }
    private function source(string $filename, Domx_Path $xpath): Source
    {
        $baseline = null;
        $restrict_notices = false;
        $restrict_warnings = false;
        $ignore_suppression_of_deprecations = false;
        $ignore_suppression_of_php_deprecations = false;
        $ignore_suppression_of_errors = false;
        $ignore_suppression_of_notices = false;
        $ignore_suppression_of_php_notices = false;
        $ignore_suppression_of_warnings = false;
        $ignore_suppression_of_php_warnings = false;
        $ignore_self_deprecations = false;
        $ignore_direct_deprecations = false;
        $ignore_indirect_deprecations = false;
        $identify_issue_trigger = true;
        $element = $this->element($xpath, 'source');
        if ($element !== null) {
            $baseline = $this->parse_string_attribute($element, 'baseline');
            if ($baseline !== null) {
                $baseline = $this->to_absolute_path($filename, $baseline);
            }
            $restrict_notices = $this->parse_boolean_attribute($element, 'restrictNotices', false);
            $restrict_warnings = $this->parse_boolean_attribute($element, 'restrictWarnings', false);
            $ignore_suppression_of_deprecations = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfDeprecations', false);
            $ignore_suppression_of_php_deprecations = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfPhpDeprecations', false);
            $ignore_suppression_of_errors = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfErrors', false);
            $ignore_suppression_of_notices = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfNotices', false);
            $ignore_suppression_of_php_notices = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfPhpNotices', false);
            $ignore_suppression_of_warnings = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfWarnings', false);
            $ignore_suppression_of_php_warnings = $this->parse_boolean_attribute($element, 'ignoreSuppressionOfPhpWarnings', false);
            $ignore_self_deprecations = $this->parse_boolean_attribute($element, 'ignoreSelfDeprecations', false);
            $ignore_direct_deprecations = $this->parse_boolean_attribute($element, 'ignoreDirectDeprecations', false);
            $ignore_indirect_deprecations = $this->parse_boolean_attribute($element, 'ignoreIndirectDeprecations', false);
            $identify_issue_trigger = $this->parse_boolean_attribute($element, 'identifyIssueTrigger', true);
        }
        $deprecation_triggers = ['functions' => [], 'methods' => []];
        $function_nodes = $xpath->query('source/deprecationTrigger/function');
        assert($function_nodes instanceof Dom_Node_List);
        foreach ($function_nodes as $function_node) {
            assert($function_node instanceof Dom_Element);
            $deprecation_triggers['functions'][] = $function_node->text_content;
        }
        $method_nodes = $xpath->query('source/deprecationTrigger/method');
        assert($method_nodes instanceof Dom_Node_List);
        foreach ($method_nodes as $method_node) {
            assert($method_node instanceof Dom_Element);
            $deprecation_triggers['methods'][] = $method_node->text_content;
        }
        $issue_trigger_resolvers = [];
        $issue_trigger_resolver_nodes = $xpath->query('source/issueTriggerResolvers/issueTriggerResolver');
        assert($issue_trigger_resolver_nodes instanceof Dom_Node_List);
        foreach ($issue_trigger_resolver_nodes as $node) {
            assert($node instanceof Dom_Element);
            $issue_trigger_resolvers[] = $node->get_attribute('className');
        }
        return new Source($baseline, false, $this->read_filter_directories($filename, $xpath, 'source/include/directory'), $this->read_filter_files($filename, $xpath, 'source/include/file'), $this->read_filter_directories($filename, $xpath, 'source/exclude/directory'), $this->read_filter_files($filename, $xpath, 'source/exclude/file'), $restrict_notices, $restrict_warnings, $ignore_suppression_of_deprecations, $ignore_suppression_of_php_deprecations, $ignore_suppression_of_errors, $ignore_suppression_of_notices, $ignore_suppression_of_php_notices, $ignore_suppression_of_warnings, $ignore_suppression_of_php_warnings, $deprecation_triggers, $ignore_self_deprecations, $ignore_direct_deprecations, $ignore_indirect_deprecations, $identify_issue_trigger, $issue_trigger_resolvers);
    }
    private function code_coverage(string $filename, Domx_Path $xpath): Code_Coverage
    {
        $path_coverage = false;
        $include_uncovered_files = true;
        $ignore_deprecated_code_units = false;
        $disable_code_coverage_ignore = false;
        $element = $this->element($xpath, 'coverage');
        if ($element !== null) {
            $path_coverage = $this->parse_boolean_attribute($element, 'pathCoverage', false);
            $include_uncovered_files = $this->parse_boolean_attribute($element, 'includeUncoveredFiles', true);
            $ignore_deprecated_code_units = $this->parse_boolean_attribute($element, 'ignoreDeprecatedCodeUnits', false);
            $disable_code_coverage_ignore = $this->parse_boolean_attribute($element, 'disableCodeCoverageIgnore', false);
        }
        $clover = null;
        $element = $this->element($xpath, 'coverage/report/clover');
        if ($element !== null) {
            $clover = new Clover(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $cobertura = null;
        $element = $this->element($xpath, 'coverage/report/cobertura');
        if ($element !== null) {
            $cobertura = new Cobertura(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $crap4j = null;
        $element = $this->element($xpath, 'coverage/report/crap4j');
        if ($element !== null) {
            $crap4j = new Crap4j(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))), $this->parse_integer_attribute($element, 'threshold', 30));
        }
        $html = null;
        $element = $this->element($xpath, 'coverage/report/html');
        if ($element !== null) {
            $default_colors = Colors::default();
            $default_thresholds = Thresholds::default();
            $output_directory = $this->parse_string_attribute($element, 'outputDirectory');
            if ($output_directory !== null) {
                $output_directory = new Directory($this->to_absolute_path($filename, $output_directory));
            }
            $html = new Code_Coverage_Html($output_directory, $this->parse_integer_attribute($element, 'lowUpperBound', $default_thresholds->low_upper_bound()), $this->parse_integer_attribute($element, 'highLowerBound', $default_thresholds->high_lower_bound()), $this->parse_string_attribute_with_default($element, 'colorSuccessLow', $default_colors->success_low()), $this->parse_string_attribute_with_default($element, 'colorSuccessLowDark', $default_colors->success_low_dark()), $this->parse_string_attribute_with_default($element, 'colorSuccessMedium', $default_colors->success_medium()), $this->parse_string_attribute_with_default($element, 'colorSuccessMediumDark', $default_colors->success_medium_dark()), $this->parse_string_attribute_with_default($element, 'colorSuccessHigh', $default_colors->success_high()), $this->parse_string_attribute_with_default($element, 'colorSuccessHighDark', $default_colors->success_high_dark()), $this->parse_string_attribute_with_default($element, 'colorSuccessBar', $default_colors->success_bar()), $this->parse_string_attribute_with_default($element, 'colorSuccessBarDark', $default_colors->success_bar_dark()), $this->parse_string_attribute_with_default($element, 'colorWarning', $default_colors->warning()), $this->parse_string_attribute_with_default($element, 'colorWarningDark', $default_colors->warning_dark()), $this->parse_string_attribute_with_default($element, 'colorWarningBar', $default_colors->warning_bar()), $this->parse_string_attribute_with_default($element, 'colorWarningBarDark', $default_colors->warning_bar_dark()), $this->parse_string_attribute_with_default($element, 'colorDanger', $default_colors->danger()), $this->parse_string_attribute_with_default($element, 'colorDangerDark', $default_colors->danger_dark()), $this->parse_string_attribute_with_default($element, 'colorDangerBar', $default_colors->danger_bar()), $this->parse_string_attribute_with_default($element, 'colorDangerBarDark', $default_colors->danger_bar_dark()), $this->parse_string_attribute_with_default($element, 'colorBreadcrumbs', $default_colors->breadcrumbs()), $this->parse_string_attribute_with_default($element, 'colorBreadcrumbsDark', $default_colors->breadcrumbs_dark()), $this->parse_string_attribute($element, 'customCssFile'));
        }
        $open_clover = null;
        $element = $this->element($xpath, 'coverage/report/openclover');
        if ($element !== null) {
            $open_clover = new Open_Clover(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $php = null;
        $element = $this->element($xpath, 'coverage/report/php');
        if ($element !== null) {
            $php = new Code_Coverage_Php(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))));
        }
        $text = null;
        $element = $this->element($xpath, 'coverage/report/text');
        if ($element !== null) {
            $text = new Code_Coverage_Text(new File($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputFile'))), $this->parse_boolean_attribute($element, 'showUncoveredFiles', false), $this->parse_boolean_attribute($element, 'showOnlySummary', false));
        }
        $xml = null;
        $element = $this->element($xpath, 'coverage/report/xml');
        if ($element !== null) {
            $xml = new Code_Coverage_Xml(new Directory($this->to_absolute_path($filename, (string) $this->parse_string_attribute($element, 'outputDirectory'))), $this->parse_boolean_attribute($element, 'includeSource', true));
        }
        return new Code_Coverage($path_coverage, $include_uncovered_files, $ignore_deprecated_code_units, $disable_code_coverage_ignore, $clover, $cobertura, $crap4j, $html, $open_clover, $php, $text, $xml);
    }
    private function boolean_from_string(string $value, bool $default): bool
    {
        if (strtolower($value) === 'false') {
            return false;
        }
        if (strtolower($value) === 'true') {
            return true;
        }
        return $default;
    }
    private function value_from_string(string $value): bool|string
    {
        if (strtolower($value) === 'false') {
            return false;
        }
        if (strtolower($value) === 'true') {
            return true;
        }
        return $value;
    }
    private function read_filter_directories(string $filename, Domx_Path $xpath, string $query): Filter_Directory_Collection
    {
        $directories = [];
        $directory_nodes = $xpath->query($query);
        assert($directory_nodes instanceof Dom_Node_List);
        foreach ($directory_nodes as $directory_node) {
            assert($directory_node instanceof Dom_Element);
            $directory_path = $directory_node->text_content;
            if ($directory_path === '') {
                continue;
            }
            $directories[] = new Filter_Directory($this->to_absolute_path($filename, $directory_path), $directory_node->has_attribute('prefix') ? $directory_node->get_attribute('prefix') : '', $directory_node->has_attribute('suffix') ? $directory_node->get_attribute('suffix') : '.php', !$directory_node->has_attribute('includeInCodeCoverage') || $directory_node->get_attribute('includeInCodeCoverage') !== 'false');
        }
        return Filter_Directory_Collection::from_array($directories);
    }
    private function read_filter_files(string $filename, Domx_Path $xpath, string $query): Filter_File_Collection
    {
        $files = [];
        $file_nodes = $xpath->query($query);
        assert($file_nodes instanceof Dom_Node_List);
        foreach ($file_nodes as $file_node) {
            assert($file_node instanceof Dom_Element);
            $file_path = $file_node->text_content;
            if ($file_path !== '') {
                $files[] = new Filter_File($this->to_absolute_path($filename, $file_path), !$file_node->has_attribute('includeInCodeCoverage') || $file_node->get_attribute('includeInCodeCoverage') !== 'false');
            }
        }
        return Filter_File_Collection::from_array($files);
    }
    private function groups(Domx_Path $xpath): Groups
    {
        $include = [];
        $exclude = [];
        $group_nodes = $xpath->query('groups/include/group');
        assert($group_nodes instanceof Dom_Node_List);
        foreach ($group_nodes as $group_node) {
            assert($group_node instanceof Dom_Node);
            $include[] = new Group($group_node->text_content);
        }
        $group_nodes = $xpath->query('groups/exclude/group');
        assert($group_nodes instanceof Dom_Node_List);
        foreach ($group_nodes as $group_node) {
            assert($group_node instanceof Dom_Node);
            $exclude[] = new Group($group_node->text_content);
        }
        return new Groups(Group_Collection::from_array($include), Group_Collection::from_array($exclude));
    }
    private function parse_boolean_attribute(Dom_Element $element, string $attribute, bool $default): bool
    {
        if (!$element->has_attribute($attribute)) {
            return $default;
        }
        return $this->boolean_from_string($element->get_attribute($attribute), false);
    }
    private function parse_integer_attribute(Dom_Element $element, string $attribute, int $default): int
    {
        if (!$element->has_attribute($attribute)) {
            return $default;
        }
        return $this->parse_integer($element->get_attribute($attribute), $default);
    }
    private function parse_string_attribute(Dom_Element $element, string $attribute): ?string
    {
        if (!$element->has_attribute($attribute)) {
            return null;
        }
        return $element->get_attribute($attribute);
    }
    private function parse_string_attribute_with_default(Dom_Element $element, string $attribute, string $default): string
    {
        if (!$element->has_attribute($attribute)) {
            return $default;
        }
        return $element->get_attribute($attribute);
    }
    private function parse_integer(string $value, int $default): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        return $default;
    }
    private function php(string $filename, Domx_Path $xpath): Php
    {
        $include_paths = [];
        $include_path_nodes = $xpath->query('php/includePath');
        assert($include_path_nodes instanceof Dom_Node_List);
        foreach ($include_path_nodes as $include_path) {
            assert($include_path instanceof Dom_Node);
            $path = $include_path->text_content;
            if ($path !== '') {
                $include_paths[] = new Directory($this->to_absolute_path($filename, $path));
            }
        }
        $ini_settings = [];
        $ini_nodes = $xpath->query('php/ini');
        assert($ini_nodes instanceof Dom_Node_List);
        foreach ($ini_nodes as $ini) {
            assert($ini instanceof Dom_Element);
            $ini_settings[] = new Ini_Setting($ini->get_attribute('name'), $ini->get_attribute('value'));
        }
        $constants = [];
        $const_nodes = $xpath->query('php/const');
        assert($const_nodes instanceof Dom_Node_List);
        foreach ($const_nodes as $const_node) {
            assert($const_node instanceof Dom_Element);
            $value = $const_node->get_attribute('value');
            $constants[] = new Constant($const_node->get_attribute('name'), $this->value_from_string($value));
        }
        $variables = ['var' => [], 'env' => [], 'post' => [], 'get' => [], 'cookie' => [], 'server' => [], 'files' => [], 'request' => []];
        foreach (['var', 'env', 'post', 'get', 'cookie', 'server', 'files', 'request'] as $array) {
            $var_nodes = $xpath->query('php/' . $array);
            assert($var_nodes instanceof Dom_Node_List);
            foreach ($var_nodes as $var) {
                assert($var instanceof Dom_Element);
                $name = $var->get_attribute('name');
                $value = $var->get_attribute('value');
                $force = false;
                $verbatim = false;
                if ($var->has_attribute('force')) {
                    $force = $this->boolean_from_string($var->get_attribute('force'), false);
                }
                if ($var->has_attribute('verbatim')) {
                    $verbatim = $this->boolean_from_string($var->get_attribute('verbatim'), false);
                }
                if (!$verbatim) {
                    $value = $this->value_from_string($value);
                }
                $variables[$array][] = new Variable($name, $value, $force);
            }
        }
        return new Php(Directory_Collection::from_array($include_paths), Ini_Setting_Collection::from_array($ini_settings), Constant_Collection::from_array($constants), Variable_Collection::from_array($variables['var']), Variable_Collection::from_array($variables['env']), Variable_Collection::from_array($variables['post']), Variable_Collection::from_array($variables['get']), Variable_Collection::from_array($variables['cookie']), Variable_Collection::from_array($variables['server']), Variable_Collection::from_array($variables['files']), Variable_Collection::from_array($variables['request']));
    }
    private function phpunit(string $filename, Dom_Document $document, Domx_Path $xpath): Php_Unit
    {
        $execution_order = Test_Suite_Sorter::ORDER_DEFAULT;
        $defects_first = false;
        $resolve_dependencies = $this->parse_boolean_attribute($document->document_element, 'resolveDependencies', true);
        if ($document->document_element->has_attribute('executionOrder')) {
            foreach (explode(',', $document->document_element->get_attribute('executionOrder')) as $order) {
                switch ($order) {
                    case 'default':
                        $execution_order = Test_Suite_Sorter::ORDER_DEFAULT;
                        $defects_first = false;
                        $resolve_dependencies = true;
                        break;
                    case 'depends':
                        $resolve_dependencies = true;
                        break;
                    case 'no-depends':
                        $resolve_dependencies = false;
                        break;
                    case 'defects':
                        $defects_first = true;
                        break;
                    case 'duration':
                        $execution_order = Test_Suite_Sorter::ORDER_DURATION;
                        break;
                    case 'random':
                        $execution_order = Test_Suite_Sorter::ORDER_RANDOMIZED;
                        break;
                    case 'reverse':
                        $execution_order = Test_Suite_Sorter::ORDER_REVERSED;
                        break;
                    case 'size':
                        $execution_order = Test_Suite_Sorter::ORDER_SIZE;
                        break;
                }
            }
        }
        $cache_directory = $this->parse_string_attribute($document->document_element, 'cacheDirectory');
        if ($cache_directory !== null) {
            $cache_directory = $this->to_absolute_path($filename, $cache_directory);
        }
        $bootstrap = $this->parse_string_attribute($document->document_element, 'bootstrap');
        if ($bootstrap !== null) {
            $bootstrap = $this->to_absolute_path($filename, $bootstrap);
        }
        $extensions_directory = $this->parse_string_attribute($document->document_element, 'extensionsDirectory');
        if ($extensions_directory !== null) {
            $extensions_directory = $this->to_absolute_path($filename, $extensions_directory);
        }
        $backup_static_properties = false;
        if ($document->document_element->has_attribute('backupStaticProperties')) {
            $backup_static_properties = $this->parse_boolean_attribute($document->document_element, 'backupStaticProperties', false);
        }
        $require_coverage_metadata = false;
        if ($document->document_element->has_attribute('requireCoverageMetadata')) {
            $require_coverage_metadata = $this->parse_boolean_attribute($document->document_element, 'requireCoverageMetadata', false);
        }
        $require_sealed_mock_objects = false;
        if ($document->document_element->has_attribute('requireSealedMockObjects')) {
            $require_sealed_mock_objects = $this->parse_boolean_attribute($document->document_element, 'requireSealedMockObjects', false);
        }
        $be_strict_about_coverage_metadata = false;
        if ($document->document_element->has_attribute('beStrictAboutCoverageMetadata')) {
            $be_strict_about_coverage_metadata = $this->parse_boolean_attribute($document->document_element, 'beStrictAboutCoverageMetadata', false);
        }
        $shorten_arrays_for_export_threshold = $this->parse_integer_attribute($document->document_element, 'shortenArraysForExportThreshold', 10);
        if ($shorten_arrays_for_export_threshold < 0) {
            $shorten_arrays_for_export_threshold = 0;
        }
        return new Php_Unit($cache_directory, $this->parse_boolean_attribute($document->document_element, 'cacheResult', true), $this->parse_columns($document), $this->parse_colors($document), $this->parse_boolean_attribute($document->document_element, 'stderr', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnAllIssues', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnIncompleteTests', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnSkippedTests', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnTestsThatTriggerDeprecations', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnPhpunitDeprecations', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnPhpunitNotices', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnTestsThatTriggerErrors', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnTestsThatTriggerNotices', false), $this->parse_boolean_attribute($document->document_element, 'displayDetailsOnTestsThatTriggerWarnings', false), $this->parse_boolean_attribute($document->document_element, 'reverseDefectList', false), $require_coverage_metadata, $require_sealed_mock_objects, $bootstrap, $this->bootstrap_for_test_suite($filename, $xpath), $this->parse_boolean_attribute($document->document_element, 'processIsolation', false), $this->parse_boolean_attribute($document->document_element, 'failOnAllIssues', false), $this->parse_boolean_attribute($document->document_element, 'failOnDeprecation', false), $this->parse_boolean_attribute($document->document_element, 'failOnPhpunitDeprecation', false), $this->parse_boolean_attribute($document->document_element, 'failOnPhpunitNotice', false), $this->parse_boolean_attribute($document->document_element, 'failOnPhpunitWarning', true), $this->parse_boolean_attribute($document->document_element, 'failOnEmptyTestSuite', false), $this->parse_boolean_attribute($document->document_element, 'failOnIncomplete', false), $this->parse_boolean_attribute($document->document_element, 'failOnNotice', false), $this->parse_boolean_attribute($document->document_element, 'failOnRisky', false), $this->parse_boolean_attribute($document->document_element, 'failOnSkipped', false), $this->parse_boolean_attribute($document->document_element, 'failOnWarning', false), $this->parse_boolean_attribute($document->document_element, 'stopOnDefect', false), $this->parse_boolean_attribute($document->document_element, 'stopOnDeprecation', false), $this->parse_boolean_attribute($document->document_element, 'stopOnError', false), $this->parse_boolean_attribute($document->document_element, 'stopOnFailure', false), $this->parse_boolean_attribute($document->document_element, 'stopOnIncomplete', false), $this->parse_boolean_attribute($document->document_element, 'stopOnNotice', false), $this->parse_boolean_attribute($document->document_element, 'stopOnRisky', false), $this->parse_boolean_attribute($document->document_element, 'stopOnSkipped', false), $this->parse_boolean_attribute($document->document_element, 'stopOnWarning', false), $extensions_directory, $this->parse_boolean_attribute($document->document_element, 'beStrictAboutChangesToGlobalState', false), $this->parse_boolean_attribute($document->document_element, 'beStrictAboutOutputDuringTests', false), $this->parse_boolean_attribute($document->document_element, 'beStrictAboutTestsThatDoNotTestAnything', true), $be_strict_about_coverage_metadata, $this->parse_boolean_attribute($document->document_element, 'enforceTimeLimit', false), $this->parse_integer_attribute($document->document_element, 'defaultTimeLimit', 1), $this->parse_integer_attribute($document->document_element, 'timeoutForSmallTests', 1), $this->parse_integer_attribute($document->document_element, 'timeoutForMediumTests', 10), $this->parse_integer_attribute($document->document_element, 'timeoutForLargeTests', 60), $this->parse_string_attribute($document->document_element, 'defaultTestSuite'), $execution_order, $resolve_dependencies, $defects_first, $this->parse_boolean_attribute($document->document_element, 'backupGlobals', false), $backup_static_properties, $this->parse_boolean_attribute($document->document_element, 'testdox', false), $this->parse_boolean_attribute($document->document_element, 'testdoxSummary', false), $this->parse_boolean_attribute($document->document_element, 'controlGarbageCollector', false), $this->parse_integer_attribute($document->document_element, 'numberOfTestsBeforeGarbageCollection', 100), $shorten_arrays_for_export_threshold);
    }
    private function parse_colors(Dom_Document $document): string
    {
        $colors = Configuration::COLOR_DEFAULT;
        if ($document->document_element->has_attribute('colors')) {
            /* only allow boolean for compatibility with previous versions
               'always' only allowed from command line */
            if ($this->boolean_from_string($document->document_element->get_attribute('colors'), false)) {
                $colors = Configuration::COLOR_AUTO;
            } else {
                $colors = Configuration::COLOR_NEVER;
            }
        }
        return $colors;
    }
    private function parse_columns(Dom_Document $document): int|string
    {
        $columns = 80;
        if ($document->document_element->has_attribute('columns')) {
            $columns = $document->document_element->get_attribute('columns');
            if ($columns !== 'max') {
                $columns = $this->parse_integer($columns, 80);
            }
        }
        return $columns;
    }
    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function bootstrap_for_test_suite(string $filename, Domx_Path $xpath): array
    {
        $bootstrap_for_test_suite = [];
        foreach ($this->parse_test_suite_elements($xpath) as $element) {
            if (!$element->has_attribute('bootstrap')) {
                continue;
            }
            $name = $element->get_attribute('name');
            $bootstrap = $element->get_attribute('bootstrap');
            assert($name !== '');
            assert($bootstrap !== '');
            $bootstrap_for_test_suite[$name] = $this->to_absolute_path($filename, $bootstrap);
        }
        return $bootstrap_for_test_suite;
    }
    private function test_suite(string $filename, Domx_Path $xpath): Test_Suite_Collection
    {
        $test_suites = [];
        foreach ($this->parse_test_suite_elements($xpath) as $element) {
            $exclude = [];
            foreach ($element->get_elements_by_tag_name('exclude') as $exclude_node) {
                $exclude_file = $exclude_node->text_content;
                if ($exclude_file !== '') {
                    $exclude[] = new File($this->to_absolute_path($filename, $exclude_file));
                }
            }
            $directories = [];
            foreach ($element->get_elements_by_tag_name('directory') as $directory_node) {
                assert($directory_node instanceof Dom_Element);
                $directory = $directory_node->text_content;
                if ($directory === '') {
                    continue;
                }
                $prefix = '';
                if ($directory_node->has_attribute('prefix')) {
                    $prefix = $directory_node->get_attribute('prefix');
                }
                $suffix = 'Test.php';
                if ($directory_node->has_attribute('suffix')) {
                    $suffix = $directory_node->get_attribute('suffix');
                }
                $php_version = PHP_VERSION;
                if ($directory_node->has_attribute('phpVersion')) {
                    $php_version = $directory_node->get_attribute('phpVersion');
                }
                $php_version_operator = new Version_Comparison_Operator('>=');
                if ($directory_node->has_attribute('phpVersionOperator')) {
                    $php_version_operator = new Version_Comparison_Operator($directory_node->get_attribute('phpVersionOperator'));
                }
                $groups = [];
                if ($directory_node->has_attribute('groups')) {
                    foreach (explode(',', $directory_node->get_attribute('groups')) as $group) {
                        $group = trim($group);
                        if ($group === '') {
                            continue;
                        }
                        $groups[] = $group;
                    }
                }
                $directories[] = new Test_Directory($this->to_absolute_path($filename, $directory), $prefix, $suffix, $php_version, $php_version_operator, $groups);
            }
            $files = [];
            foreach ($element->get_elements_by_tag_name('file') as $file_node) {
                assert($file_node instanceof Dom_Element);
                $file = $file_node->text_content;
                if ($file === '') {
                    continue;
                }
                $php_version = PHP_VERSION;
                if ($file_node->has_attribute('phpVersion')) {
                    $php_version = $file_node->get_attribute('phpVersion');
                }
                $php_version_operator = new Version_Comparison_Operator('>=');
                if ($file_node->has_attribute('phpVersionOperator')) {
                    $php_version_operator = new Version_Comparison_Operator($file_node->get_attribute('phpVersionOperator'));
                }
                $groups = [];
                if ($file_node->has_attribute('groups')) {
                    foreach (explode(',', $file_node->get_attribute('groups')) as $group) {
                        $group = trim($group);
                        if ($group === '') {
                            continue;
                        }
                        $groups[] = $group;
                    }
                }
                $files[] = new Test_File($this->to_absolute_path($filename, $file), $php_version, $php_version_operator, $groups);
            }
            $name = $element->get_attribute('name');
            assert($name !== '');
            $test_suites[] = new Test_Suite_Configuration($name, Test_Directory_Collection::from_array($directories), Test_File_Collection::from_array($files), File_Collection::from_array($exclude));
        }
        return Test_Suite_Collection::from_array($test_suites);
    }
    /**
     * @return list<DOMElement>
     */
    private function parse_test_suite_elements(Domx_Path $xpath): array
    {
        $elements = [];
        $test_suite_nodes = $xpath->query('testsuites/testsuite');
        assert($test_suite_nodes instanceof Dom_Node_List);
        if ($test_suite_nodes->length === 0) {
            $test_suite_nodes = $xpath->query('testsuite');
            assert($test_suite_nodes instanceof Dom_Node_List);
        }
        if ($test_suite_nodes->length === 1) {
            $element = $test_suite_nodes->item(0);
            assert($element instanceof Dom_Element);
            $elements[] = $element;
        } else {
            foreach ($test_suite_nodes as $test_suite_node) {
                assert($test_suite_node instanceof Dom_Element);
                $elements[] = $test_suite_node;
            }
        }
        return $elements;
    }
    private function element(Domx_Path $xpath, string $element): ?Dom_Element
    {
        $nodes = $xpath->query($element);
        assert($nodes instanceof Dom_Node_List);
        if ($nodes->length === 1) {
            $node = $nodes->item(0);
            assert($node instanceof Dom_Element);
            return $node;
        }
        return null;
    }
}