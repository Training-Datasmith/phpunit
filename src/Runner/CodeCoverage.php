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
namespace Php_Unit\Runner;

use function assert;
use DateTimeImmutable;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Text_Ui\Configuration\Code_Coverage_Filter_Registry;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Output\Printer;
use Php_Unit\Util\Filesystem;
use Sebastian_Bergmann\Code_Coverage\Driver\Driver;
use Sebastian_Bergmann\Code_Coverage\Driver\Selector;
use Sebastian_Bergmann\Code_Coverage\Exception as CodeCoverageException;
use Sebastian_Bergmann\Code_Coverage\Filter;
use Sebastian_Bergmann\Code_Coverage\Report\Facade as ReportFacade;
use Sebastian_Bergmann\Code_Coverage\Report\Html\Colors;
use Sebastian_Bergmann\Code_Coverage\Report\Html\Custom_Css_File;
use Sebastian_Bergmann\Code_Coverage\Report\Thresholds;
use Sebastian_Bergmann\Code_Coverage\Serialization\Serializer;
use Sebastian_Bergmann\Code_Coverage\Static_Analysis\Cache_Warmer;
use Sebastian_Bergmann\Code_Coverage\Test\Target\Target_Collection;
use Sebastian_Bergmann\Code_Coverage\Test\Target\Validation_Failure;
use Sebastian_Bergmann\Code_Coverage\Test\Test_Size;
use Sebastian_Bergmann\Code_Coverage\Test\Test_Status;
use Sebastian_Bergmann\Code_Coverage\Version as CodeCoverageVersion;
use Sebastian_Bergmann\Comparator\Comparator;
use Sebastian_Bergmann\Environment\Runtime;
use Sebastian_Bergmann\Timer\No_Active_Timer_Exception;
use Sebastian_Bergmann\Timer\Timer;
use function sprintf;
use function sys_get_temp_dir;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final class Code_Coverage
{
    private static ?self $instance = null;
    private ?\Sebastian_Bergmann\Code_Coverage\Code_Coverage $code_coverage = null;
    /**
     * @phpstan-ignore property.internalClass
     */
    private ?Driver $driver = null;
    private bool $collecting = false;
    private ?Test_Case $test = null;
    private ?Timer $timer = null;
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function init(Configuration $configuration, Code_Coverage_Filter_Registry $code_coverage_filter_registry, bool $extension_requires_code_coverage_collection): Code_Coverage_Initialization_Status
    {
        $code_coverage_filter_registry->init($configuration);
        if (!$configuration->has_coverage_report() && !$extension_requires_code_coverage_collection) {
            return Code_Coverage_Initialization_Status::NOT_REQUESTED;
        }
        $this->activate($code_coverage_filter_registry->get(), $configuration->path_coverage());
        if (!$this->is_active()) {
            return Code_Coverage_Initialization_Status::FAILED;
        }
        if ($configuration->has_coverage_cache_directory()) {
            $coverage_cache_directory = $configuration->coverage_cache_directory();
        } else {
            $candidate = sys_get_temp_dir() . '/phpunit-code-coverage-cache';
            if (Filesystem::create_directory($candidate)) {
                $coverage_cache_directory = $candidate;
            }
        }
        if (isset($coverage_cache_directory)) {
            $this->code_coverage()->cache_static_analysis($coverage_cache_directory);
        }
        $this->code_coverage()->exclude_subclasses_of_this_class_from_unintentionally_covered_code_check(Comparator::class);
        if ($configuration->strict_coverage()) {
            $this->code_coverage()->enable_check_for_unintentionally_covered_code();
        }
        if ($configuration->ignore_deprecated_code_units_from_code_coverage()) {
            $this->code_coverage()->ignore_deprecated_code();
        } else {
            $this->code_coverage()->do_not_ignore_deprecated_code();
        }
        if ($configuration->disable_code_coverage_ignore()) {
            $this->code_coverage()->disable_annotations_for_ignoring_code();
        } else {
            $this->code_coverage()->enable_annotations_for_ignoring_code();
        }
        if ($configuration->include_uncovered_files()) {
            $this->code_coverage()->include_uncovered_files();
        } else {
            $this->code_coverage()->exclude_uncovered_files();
        }
        if ($code_coverage_filter_registry->get()->is_empty()) {
            if (!$code_coverage_filter_registry->configured()) {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning('No filter is configured, code coverage will not be processed');
            } else {
                Event_Facade::emitter()->test_runner_triggered_phpunit_warning('Configured filter does not match any files, code coverage will not be processed');
            }
            $this->deactivate();
        }
        if (isset($coverage_cache_directory) && $configuration->include_uncovered_files()) {
            Event_Facade::emitter()->test_runner_started_static_analysis_for_code_coverage();
            /** @phpstan-ignore new.internalClass,method.internalClass */
            $statistics = (new Cache_Warmer())->warm_cache($coverage_cache_directory, !$configuration->disable_code_coverage_ignore(), $configuration->ignore_deprecated_code_units_from_code_coverage(), $code_coverage_filter_registry->get());
            Event_Facade::emitter()->test_runner_finished_static_analysis_for_code_coverage($statistics['cacheHits'], $statistics['cacheMisses']);
        }
        return Code_Coverage_Initialization_Status::SUCCEEDED;
    }
    /**
     * @phpstan-assert-if-true !null $this->codeCoverage
     */
    public function is_active(): bool
    {
        return $this->code_coverage !== null;
    }
    public function code_coverage(): \Sebastian_Bergmann\Code_Coverage\Code_Coverage
    {
        return $this->code_coverage;
    }
    /**
     * @return non-empty-string
     */
    public function driver_name_and_version(): string
    {
        return $this->driver->name_and_version();
    }
    public function start(Test_Case $test): void
    {
        if ($this->collecting) {
            return;
        }
        $size = Test_Size::Unknown;
        if ($test->size()->is_small()) {
            $size = Test_Size::Small;
        } elseif ($test->size()->is_medium()) {
            $size = Test_Size::Medium;
        } elseif ($test->size()->is_large()) {
            $size = Test_Size::Large;
        }
        $this->test = $test;
        $this->code_coverage->start($test->value_object_for_events()->id(), $size);
        $this->collecting = true;
        $this->timer()->start();
    }
    public function stop(bool $append, null|false|Target_Collection $covers = null, ?Target_Collection $uses = null): void
    {
        if (!$this->collecting) {
            return;
        }
        $time = $this->timer()->stop()->as_seconds();
        $status = Test_Status::Unknown;
        $this->collecting = false;
        if ($this->test !== null) {
            if ($this->test->status()->is_success()) {
                $status = Test_Status::Success;
            } else {
                $status = Test_Status::Failure;
            }
        }
        if ($covers instanceof Target_Collection) {
            $result = $this->code_coverage->validate($covers);
            if ($result->is_failure()) {
                assert($result instanceof Validation_Failure);
                Event_Facade::emitter()->test_triggered_phpunit_warning($this->test->value_object_for_events(), $result->message());
                $append = false;
            }
        }
        if ($uses instanceof Target_Collection) {
            $result = $this->code_coverage->validate($uses);
            if ($result->is_failure()) {
                assert($result instanceof Validation_Failure);
                Event_Facade::emitter()->test_triggered_phpunit_warning($this->test->value_object_for_events(), $result->message());
                $append = false;
            }
        }
        $this->code_coverage->stop($append, $status, $covers, $uses, $time);
        $this->test = null;
    }
    public function deactivate(): void
    {
        $this->driver = null;
        $this->code_coverage = null;
        $this->test = null;
    }
    public function generate_reports(Printer $printer, Configuration $configuration): void
    {
        if (!$this->is_active()) {
            return;
        }
        if ($configuration->has_coverage_php()) {
            $this->code_coverage_generation_start($printer, 'PHP');
            $serializer = new Serializer();
            $serializer->serialize($configuration->coverage_php(), $this->code_coverage(), $configuration->include_git_information());
            $this->code_coverage_generation_succeeded($printer);
            unset($serializer);
        }
        $facade = Report_Facade::from_object($this->code_coverage());
        if ($configuration->has_coverage_clover()) {
            $this->code_coverage_generation_start($printer, 'Clover XML');
            try {
                $facade->render_clover($configuration->coverage_clover(), 'Clover Coverage');
                $this->code_coverage_generation_succeeded($printer);
            } catch (Code_Coverage_Exception $e) {
                $this->code_coverage_generation_failed($printer, $e);
            }
        }
        if ($configuration->has_coverage_open_clover()) {
            $this->code_coverage_generation_start($printer, 'OpenClover XML');
            try {
                $facade->render_open_clover($configuration->coverage_open_clover(), 'OpenClover Coverage');
                $this->code_coverage_generation_succeeded($printer);
            } catch (Code_Coverage_Exception $e) {
                $this->code_coverage_generation_failed($printer, $e);
            }
        }
        if ($configuration->has_coverage_cobertura()) {
            $this->code_coverage_generation_start($printer, 'Cobertura XML');
            try {
                $facade->render_cobertura($configuration->coverage_cobertura());
                $this->code_coverage_generation_succeeded($printer);
            } catch (Code_Coverage_Exception $e) {
                $this->code_coverage_generation_failed($printer, $e);
            }
        }
        if ($configuration->has_coverage_crap4j()) {
            $this->code_coverage_generation_start($printer, 'Crap4J XML');
            try {
                $facade->render_crap4j($configuration->coverage_crap4j(), $configuration->coverage_crap4j_threshold());
                $this->code_coverage_generation_succeeded($printer);
            } catch (Code_Coverage_Exception $e) {
                $this->code_coverage_generation_failed($printer, $e);
            }
        }
        if ($configuration->has_coverage_html()) {
            $this->code_coverage_generation_start($printer, 'HTML');
            try {
                $custom_css_file = Custom_Css_File::default();
                if ($configuration->has_coverage_html_custom_css_file()) {
                    $custom_css_file = Custom_Css_File::from($configuration->coverage_html_custom_css_file());
                }
                $facade->render_html($configuration->coverage_html(), sprintf(' and <a href="https://phpunit.de/">PHPUnit %s</a>', Version::id()), Colors::from($configuration->coverage_html_color_success_low(), $configuration->coverage_html_color_success_low_dark(), $configuration->coverage_html_color_success_medium(), $configuration->coverage_html_color_success_medium_dark(), $configuration->coverage_html_color_success_high(), $configuration->coverage_html_color_success_high_dark(), $configuration->coverage_html_color_success_bar(), $configuration->coverage_html_color_success_bar_dark(), $configuration->coverage_html_color_warning(), $configuration->coverage_html_color_warning_dark(), $configuration->coverage_html_color_warning_bar(), $configuration->coverage_html_color_warning_bar_dark(), $configuration->coverage_html_color_danger(), $configuration->coverage_html_color_danger_dark(), $configuration->coverage_html_color_danger_bar(), $configuration->coverage_html_color_danger_bar_dark(), $configuration->coverage_html_color_breadcrumbs(), $configuration->coverage_html_color_breadcrumbs_dark()), Thresholds::from($configuration->coverage_html_low_upper_bound(), $configuration->coverage_html_high_lower_bound()), $custom_css_file);
                $this->code_coverage_generation_succeeded($printer);
            } catch (Code_Coverage_Exception $e) {
                $this->code_coverage_generation_failed($printer, $e);
            }
        }
        if ($configuration->has_coverage_text()) {
            if ($configuration->coverage_text() === 'php://stdout') {
                if (!$configuration->no_output() && !$configuration->debug()) {
                    $printer->print($facade->render_text(null, Thresholds::default(), $configuration->coverage_text_show_uncovered_files(), $configuration->coverage_text_show_only_summary(), $configuration->colors()));
                }
            } else {
                $facade->render_text($configuration->coverage_text(), Thresholds::default(), $configuration->coverage_text_show_uncovered_files(), $configuration->coverage_text_show_only_summary(), $configuration->colors());
            }
        }
        if ($configuration->has_coverage_xml()) {
            $this->code_coverage_generation_start($printer, 'PHPUnit XML');
            try {
                $driver_information = $this->code_coverage->driver_information();
                $facade->render_xml($configuration->coverage_xml(), $configuration->coverage_xml_include_source(), new Runtime(), new DateTimeImmutable(), Version::id(), Code_Coverage_Version::id(), $driver_information['name'], $driver_information['version']);
                $this->code_coverage_generation_succeeded($printer);
            } catch (Code_Coverage_Exception $e) {
                $this->code_coverage_generation_failed($printer, $e);
            }
        }
    }
    private function activate(Filter $filter, bool $path_coverage): void
    {
        try {
            if ($path_coverage) {
                $this->driver = (new Selector())->for_line_and_path_coverage($filter);
            } else {
                $this->driver = (new Selector())->for_line_coverage($filter);
            }
            $this->code_coverage = new \Sebastian_Bergmann\Code_Coverage\Code_Coverage($this->driver, $filter);
        } catch (Code_Coverage_Exception $e) {
            Event_Facade::emitter()->test_runner_triggered_phpunit_warning($e->get_message());
        }
    }
    private function code_coverage_generation_start(Printer $printer, string $format): void
    {
        $printer->print(sprintf("\nGenerating code coverage report in %s format ... ", $format));
        $this->timer()->start();
    }
    /**
     * @throws NoActiveTimerException
     */
    private function code_coverage_generation_succeeded(Printer $printer): void
    {
        $printer->print(sprintf("done [%s]\n", $this->timer()->stop()->as_string()));
    }
    /**
     * @throws NoActiveTimerException
     */
    private function code_coverage_generation_failed(Printer $printer, Code_Coverage_Exception $e): void
    {
        $printer->print(sprintf("failed [%s]\n%s\n", $this->timer()->stop()->as_string(), $e->get_message()));
    }
    private function timer(): Timer
    {
        if ($this->timer === null) {
            $this->timer = new Timer();
        }
        return $this->timer;
    }
}