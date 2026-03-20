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

use function array_filter;
use function array_merge;
use function count;
use Countable;
use IteratorAggregate;
/**
 * @template-implements IteratorAggregate<non-negative-int, Metadata>
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Metadata_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<Metadata>
     */
    private array $metadata;
    /**
     * @param list<Metadata> $metadata
     */
    public static function from_array(array $metadata): self
    {
        return new self(...$metadata);
    }
    private function __construct(Metadata ...$metadata)
    {
        $this->metadata = $metadata;
    }
    /**
     * @return list<Metadata>
     */
    public function as_array(): array
    {
        return $this->metadata;
    }
    public function count(): int
    {
        return count($this->metadata);
    }
    /**
     * @phpstan-assert-if-true 0 $this->count()
     * @phpstan-assert-if-true array{} $this->asArray()
     */
    public function is_empty(): bool
    {
        return $this->count() === 0;
    }
    /**
     * @phpstan-assert-if-true positive-int $this->count()
     * @phpstan-assert-if-true non-empty-list<Metadata> $this->asArray()
     */
    public function is_not_empty(): bool
    {
        return $this->count() > 0;
    }
    public function getIterator(): Metadata_Collection_Iterator
    {
        return new Metadata_Collection_Iterator($this);
    }
    public function merge_with(self $other): self
    {
        return new self(...array_merge($this->as_array(), $other->as_array()));
    }
    public function is_class_level(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_class_level()));
    }
    public function is_method_level(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_method_level()));
    }
    public function is_after(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_after()));
    }
    public function is_after_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_after_class()));
    }
    public function is_allow_mock_objects_without_expectations(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_allow_mock_objects_without_expectations()));
    }
    public function is_backup_globals(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_backup_globals()));
    }
    public function is_backup_static_properties(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_backup_static_properties()));
    }
    public function is_before_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_before_class()));
    }
    public function is_before(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_before()));
    }
    public function is_covers_namespace(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_namespace()));
    }
    public function is_covers_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_class()));
    }
    public function is_covers_classes_that_extend_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_classes_that_extend_class()));
    }
    public function is_covers_classes_that_implement_interface(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_classes_that_implement_interface()));
    }
    public function is_covers_trait(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_trait()));
    }
    public function is_covers_function(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_function()));
    }
    public function is_covers_method(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_method()));
    }
    public function is_exclude_global_variable_from_backup(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_exclude_global_variable_from_backup()));
    }
    public function is_exclude_static_property_from_backup(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_exclude_static_property_from_backup()));
    }
    public function is_covers_nothing(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_covers_nothing()));
    }
    public function is_data_provider(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_data_provider()));
    }
    public function is_data_provider_closure(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_data_provider_closure()));
    }
    public function is_depends(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_depends_on_class() || $metadata->is_depends_on_method()));
    }
    public function is_depends_on_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_depends_on_class()));
    }
    public function is_depends_on_method(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_depends_on_method()));
    }
    public function is_disable_return_value_generation_for_test_doubles(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_disable_return_value_generation_for_test_doubles()));
    }
    public function is_does_not_perform_assertions(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_does_not_perform_assertions()));
    }
    public function is_group(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_group()));
    }
    public function is_ignore_deprecations(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_ignore_deprecations()));
    }
    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function is_ignore_phpunit_deprecations(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_ignore_phpunit_deprecations()));
    }
    public function is_ignore_phpunit_warnings(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_ignore_phpunit_warnings()));
    }
    public function is_run_in_separate_process(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_run_in_separate_process()));
    }
    public function is_run_tests_in_separate_processes(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_run_tests_in_separate_processes()));
    }
    public function is_test(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_test()));
    }
    public function is_pre_condition(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_pre_condition()));
    }
    public function is_post_condition(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_post_condition()));
    }
    public function is_preserve_global_state(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_preserve_global_state()));
    }
    public function is_requires_method(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_method()));
    }
    public function is_requires_function(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_function()));
    }
    public function is_requires_operating_system(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_operating_system()));
    }
    public function is_requires_operating_system_family(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_operating_system_family()));
    }
    public function is_requires_php(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_php()));
    }
    public function is_requires_php_extension(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_php_extension()));
    }
    public function is_requires_phpunit(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_phpunit()));
    }
    public function is_requires_phpunit_extension(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_phpunit_extension()));
    }
    public function is_requires_environment_variable(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_environment_variable()));
    }
    public function is_with_environment_variable(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_with_environment_variable()));
    }
    public function is_requires_setting(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_requires_setting()));
    }
    public function is_test_dox(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_test_dox()));
    }
    public function is_test_dox_formatter(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_test_dox_formatter()));
    }
    public function is_test_with(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_test_with()));
    }
    public function is_uses_namespace(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_namespace()));
    }
    public function is_uses_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_class()));
    }
    public function is_uses_classes_that_extend_class(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_classes_that_extend_class()));
    }
    public function is_uses_classes_that_implement_interface(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_classes_that_implement_interface()));
    }
    public function is_uses_trait(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_trait()));
    }
    public function is_uses_function(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_function()));
    }
    public function is_uses_method(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_uses_method()));
    }
    public function is_without_error_handler(): self
    {
        return new self(...array_filter($this->metadata, static fn(Metadata $metadata): bool => $metadata->is_without_error_handler()));
    }
}