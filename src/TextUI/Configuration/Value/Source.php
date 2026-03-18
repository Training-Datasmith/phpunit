<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

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
    public function __construct(private ?string $baseline, private bool $ignoreBaseline, private FilterDirectoryCollection $includeDirectories, private FilterFileCollection $includeFiles, private FilterDirectoryCollection $excludeDirectories, private FilterFileCollection $excludeFiles, private bool $restrictNotices, private bool $restrictWarnings, private bool $ignoreSuppressionOfDeprecations, private bool $ignoreSuppressionOfPhpDeprecations, private bool $ignoreSuppressionOfErrors, private bool $ignoreSuppressionOfNotices, private bool $ignoreSuppressionOfPhpNotices, private bool $ignoreSuppressionOfWarnings, private bool $ignoreSuppressionOfPhpWarnings, private array $deprecationTriggers, private bool $ignoreSelfDeprecations, private bool $ignoreDirectDeprecations, private bool $ignoreIndirectDeprecations, private bool $identifyIssueTrigger, private array $issueTriggerResolvers = [])
    {
    }

    /**
     * @phpstan-assert-if-true !null $this->baseline
     */
    public function useBaseline(): bool
    {
        return $this->hasBaseline() && !$this->ignoreBaseline;
    }

    /**
     * @phpstan-assert-if-true !null $this->baseline
     */
    public function hasBaseline(): bool
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
        if (!$this->hasBaseline()) {
            throw new NoBaselineException;
        }

        return $this->baseline;
    }

    public function includeDirectories(): FilterDirectoryCollection
    {
        return $this->includeDirectories;
    }

    public function includeFiles(): FilterFileCollection
    {
        return $this->includeFiles;
    }

    public function excludeDirectories(): FilterDirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FilterFileCollection
    {
        return $this->excludeFiles;
    }

    public function notEmpty(): bool
    {
        if ($this->includeDirectories->notEmpty()) {
            return true;
        }
        return $this->includeFiles->notEmpty();
    }

    public function restrictNotices(): bool
    {
        return $this->restrictNotices;
    }

    public function restrictWarnings(): bool
    {
        return $this->restrictWarnings;
    }

    public function ignoreSuppressionOfDeprecations(): bool
    {
        return $this->ignoreSuppressionOfDeprecations;
    }

    public function ignoreSuppressionOfPhpDeprecations(): bool
    {
        return $this->ignoreSuppressionOfPhpDeprecations;
    }

    public function ignoreSuppressionOfErrors(): bool
    {
        return $this->ignoreSuppressionOfErrors;
    }

    public function ignoreSuppressionOfNotices(): bool
    {
        return $this->ignoreSuppressionOfNotices;
    }

    public function ignoreSuppressionOfPhpNotices(): bool
    {
        return $this->ignoreSuppressionOfPhpNotices;
    }

    public function ignoreSuppressionOfWarnings(): bool
    {
        return $this->ignoreSuppressionOfWarnings;
    }

    public function ignoreSuppressionOfPhpWarnings(): bool
    {
        return $this->ignoreSuppressionOfPhpWarnings;
    }

    /**
     * @return array{functions: list<non-empty-string>, methods: list<non-empty-string>}
     */
    public function deprecationTriggers(): array
    {
        return $this->deprecationTriggers;
    }

    public function ignoreSelfDeprecations(): bool
    {
        return $this->ignoreSelfDeprecations;
    }

    public function ignoreDirectDeprecations(): bool
    {
        return $this->ignoreDirectDeprecations;
    }

    public function ignoreIndirectDeprecations(): bool
    {
        return $this->ignoreIndirectDeprecations;
    }

    public function identifyIssueTrigger(): bool
    {
        return $this->identifyIssueTrigger;
    }

    /**
     * @return list<class-string>
     */
    public function issueTriggerResolvers(): array
    {
        return $this->issueTriggerResolvers;
    }
}
