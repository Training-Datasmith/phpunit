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
namespace Php_Unit\Text_Ui\Configuration;

use function array_keys;
use function assert;
use Sebastian_Bergmann\Code_Coverage\Filter;
/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Code_Coverage_Filter_Registry
{
    private static ?self $instance = null;
    private ?Filter $filter = null;
    private bool $configured = false;
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * @codeCoverageIgnore
     */
    public function get(): Filter
    {
        assert($this->filter !== null);
        return $this->filter;
    }
    /**
     * @codeCoverageIgnore
     */
    public function init(Configuration $configuration, bool $force = false): void
    {
        if (!$configuration->has_coverage_report() && !$force) {
            return;
        }
        if ($this->configured && !$force) {
            return;
        }
        $this->filter = new Filter();
        if ($configuration->source()->not_empty()) {
            $this->filter->include_files(array_keys((new Source_Mapper())->map_for_code_coverage($configuration->source())));
            $this->configured = true;
        }
    }
    /**
     * @codeCoverageIgnore
     */
    public function configured(): bool
    {
        return $this->configured;
    }
}