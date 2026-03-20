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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Source
{
    /**
     * @param ?non-empty-string                                                         $baseline
     * @param array{functions: list<non-empty-string>, methods: list<non-empty-string>} $deprecationTriggers
     * @param list<class-string>                                                        $issueTriggerResolvers
     */
    public function __construct(private ?string $baseline, private bool $ignore_baseline, private Filter_Directory_Collection $include_directories, private Filter_File_Collection $include_files, private Filter_Directory_Collection $exclude_directories, private Filter_File_Collection $exclude_files, private bool $restrict_notices, private bool $restrict_warnings, private bool $ignore_suppression_of_deprecations, private bool $ignore_suppression_of_php_deprecations, private bool $ignore_suppression_of_errors, private bool $ignore_suppression_of_notices, private bool $ignore_suppression_of_php_notices, private bool $ignore_suppression_of_warnings, private bool $ignore_suppression_of_php_warnings, private array $deprecation_triggers, private bool $ignore_self_deprecations, private bool $ignore_direct_deprecations, private bool $ignore_indirect_deprecations, private bool $identify_issue_trigger, private array $issue_trigger_resolvers = [])
    {
    }
    /**
     * @phpstan-assert-if-true !null $this->baseline
     */
    public function use_baseline(): bool
    {
        return $this->has_baseline() && !$this->ignore_baseline;
    }
    /**
     * @phpstan-assert-if-true !null $this->baseline
     */
    public function has_baseline(): bool
    {
        return $this->baseline !== null;
    }
    /**
     * @throws NoBaselineException
     *
     * @return non-empty-string
     */
    public function baseline(): string
    {
        if (!$this->has_baseline()) {
            throw new No_Baseline_Exception();
        }
        return $this->baseline;
    }
    public function include_directories(): Filter_Directory_Collection
    {
        return $this->include_directories;
    }
    public function include_files(): Filter_File_Collection
    {
        return $this->include_files;
    }
    public function exclude_directories(): Filter_Directory_Collection
    {
        return $this->exclude_directories;
    }
    public function exclude_files(): Filter_File_Collection
    {
        return $this->exclude_files;
    }
    public function not_empty(): bool
    {
        if ($this->include_directories->not_empty()) {
            return true;
        }
        return $this->include_files->not_empty();
    }
    public function restrict_notices(): bool
    {
        return $this->restrict_notices;
    }
    public function restrict_warnings(): bool
    {
        return $this->restrict_warnings;
    }
    public function ignore_suppression_of_deprecations(): bool
    {
        return $this->ignore_suppression_of_deprecations;
    }
    public function ignore_suppression_of_php_deprecations(): bool
    {
        return $this->ignore_suppression_of_php_deprecations;
    }
    public function ignore_suppression_of_errors(): bool
    {
        return $this->ignore_suppression_of_errors;
    }
    public function ignore_suppression_of_notices(): bool
    {
        return $this->ignore_suppression_of_notices;
    }
    public function ignore_suppression_of_php_notices(): bool
    {
        return $this->ignore_suppression_of_php_notices;
    }
    public function ignore_suppression_of_warnings(): bool
    {
        return $this->ignore_suppression_of_warnings;
    }
    public function ignore_suppression_of_php_warnings(): bool
    {
        return $this->ignore_suppression_of_php_warnings;
    }
    /**
     * @return array{functions: list<non-empty-string>, methods: list<non-empty-string>}
     */
    public function deprecation_triggers(): array
    {
        return $this->deprecation_triggers;
    }
    public function ignore_self_deprecations(): bool
    {
        return $this->ignore_self_deprecations;
    }
    public function ignore_direct_deprecations(): bool
    {
        return $this->ignore_direct_deprecations;
    }
    public function ignore_indirect_deprecations(): bool
    {
        return $this->ignore_indirect_deprecations;
    }
    public function identify_issue_trigger(): bool
    {
        return $this->identify_issue_trigger;
    }
    /**
     * @return list<class-string>
     */
    public function issue_trigger_resolvers(): array
    {
        return $this->issue_trigger_resolvers;
    }
}