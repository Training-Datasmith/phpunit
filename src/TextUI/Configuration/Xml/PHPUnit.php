<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\TextUI\XmlConfiguration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class PHPUnit
{
    /**
     * @param array<non-empty-string, non-empty-string> $bootstrapForTestSuite
     * @param ?non-empty-string                         $extensionsDirectory
     * @param non-negative-int                          $shortenArraysForExportThreshold
     */
    public function __construct(private ?string $cacheDirectory, private bool $cacheResult, private int|string $columns, private string $colors, private bool $stderr, private bool $displayDetailsOnAllIssues, private bool $displayDetailsOnIncompleteTests, private bool $displayDetailsOnSkippedTests, private bool $displayDetailsOnTestsThatTriggerDeprecations, private bool $displayDetailsOnPhpunitDeprecations, private bool $displayDetailsOnPhpunitNotices, private bool $displayDetailsOnTestsThatTriggerErrors, private bool $displayDetailsOnTestsThatTriggerNotices, private bool $displayDetailsOnTestsThatTriggerWarnings, private bool $reverseDefectList, private bool $requireCoverageMetadata, private bool $requireSealedMockObjects, private ?string $bootstrap, private array $bootstrapForTestSuite, private bool $processIsolation, private bool $failOnAllIssues, private bool $failOnDeprecation, private bool $failOnPhpunitDeprecation, private bool $failOnPhpunitNotice, private bool $failOnPhpunitWarning, private bool $failOnEmptyTestSuite, private bool $failOnIncomplete, private bool $failOnNotice, private bool $failOnRisky, private bool $failOnSkipped, private bool $failOnWarning, private bool $stopOnDefect, private bool $stopOnDeprecation, private bool $stopOnError, private bool $stopOnFailure, private bool $stopOnIncomplete, private bool $stopOnNotice, private bool $stopOnRisky, private bool $stopOnSkipped, private bool $stopOnWarning, private ?string $extensionsDirectory, private bool $beStrictAboutChangesToGlobalState, private bool $beStrictAboutOutputDuringTests, private bool $beStrictAboutTestsThatDoNotTestAnything, private bool $beStrictAboutCoverageMetadata, private bool $enforceTimeLimit, private int $defaultTimeLimit, private int $timeoutForSmallTests, private int $timeoutForMediumTests, private int $timeoutForLargeTests, private ?string $defaultTestSuite, private int $executionOrder, private bool $resolveDependencies, private bool $defectsFirst, private bool $backupGlobals, private bool $backupStaticProperties, private bool $testdoxPrinter, private bool $testdoxPrinterSummary, private bool $controlGarbageCollector, private int $numberOfTestsBeforeGarbageCollection, private int $shortenArraysForExportThreshold)
    {
    }

    /**
     * @phpstan-assert-if-true !null $this->cacheDirectory
     */
    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheDirectory(): string
    {
        if (!$this->hasCacheDirectory()) {
            throw new Exception('Cache directory is not configured');
        }

        return $this->cacheDirectory;
    }

    public function cacheResult(): bool
    {
        return $this->cacheResult;
    }

    public function columns(): int|string
    {
        return $this->columns;
    }

    public function colors(): string
    {
        return $this->colors;
    }

    public function stderr(): bool
    {
        return $this->stderr;
    }

    public function displayDetailsOnAllIssues(): bool
    {
        return $this->displayDetailsOnAllIssues;
    }

    public function displayDetailsOnIncompleteTests(): bool
    {
        return $this->displayDetailsOnIncompleteTests;
    }

    public function displayDetailsOnSkippedTests(): bool
    {
        return $this->displayDetailsOnSkippedTests;
    }

    public function displayDetailsOnTestsThatTriggerDeprecations(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerDeprecations;
    }

    public function displayDetailsOnPhpunitDeprecations(): bool
    {
        return $this->displayDetailsOnPhpunitDeprecations;
    }

    public function displayDetailsOnPhpunitNotices(): bool
    {
        return $this->displayDetailsOnPhpunitNotices;
    }

    public function displayDetailsOnTestsThatTriggerErrors(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerErrors;
    }

    public function displayDetailsOnTestsThatTriggerNotices(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerNotices;
    }

    public function displayDetailsOnTestsThatTriggerWarnings(): bool
    {
        return $this->displayDetailsOnTestsThatTriggerWarnings;
    }

    public function reverseDefectList(): bool
    {
        return $this->reverseDefectList;
    }

    public function requireCoverageMetadata(): bool
    {
        return $this->requireCoverageMetadata;
    }

    public function requireSealedMockObjects(): bool
    {
        return $this->requireSealedMockObjects;
    }

    /**
     * @phpstan-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws Exception
     */
    public function bootstrap(): string
    {
        if (!$this->hasBootstrap()) {
            throw new Exception('Bootstrap script is not configured');
        }

        return $this->bootstrap;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function bootstrapForTestSuite(): array
    {
        return $this->bootstrapForTestSuite;
    }

    public function processIsolation(): bool
    {
        return $this->processIsolation;
    }

    public function failOnAllIssues(): bool
    {
        return $this->failOnAllIssues;
    }

    public function failOnDeprecation(): bool
    {
        return $this->failOnDeprecation;
    }

    public function failOnPhpunitDeprecation(): bool
    {
        return $this->failOnPhpunitDeprecation;
    }

    public function failOnPhpunitNotice(): bool
    {
        return $this->failOnPhpunitNotice;
    }

    public function failOnPhpunitWarning(): bool
    {
        return $this->failOnPhpunitWarning;
    }

    public function failOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite;
    }

    public function failOnIncomplete(): bool
    {
        return $this->failOnIncomplete;
    }

    public function failOnNotice(): bool
    {
        return $this->failOnNotice;
    }

    public function failOnRisky(): bool
    {
        return $this->failOnRisky;
    }

    public function failOnSkipped(): bool
    {
        return $this->failOnSkipped;
    }

    public function failOnWarning(): bool
    {
        return $this->failOnWarning;
    }

    public function stopOnDefect(): bool
    {
        return $this->stopOnDefect;
    }

    public function stopOnDeprecation(): bool
    {
        return $this->stopOnDeprecation;
    }

    public function stopOnError(): bool
    {
        return $this->stopOnError;
    }

    public function stopOnFailure(): bool
    {
        return $this->stopOnFailure;
    }

    public function stopOnIncomplete(): bool
    {
        return $this->stopOnIncomplete;
    }

    public function stopOnNotice(): bool
    {
        return $this->stopOnNotice;
    }

    public function stopOnRisky(): bool
    {
        return $this->stopOnRisky;
    }

    public function stopOnSkipped(): bool
    {
        return $this->stopOnSkipped;
    }

    public function stopOnWarning(): bool
    {
        return $this->stopOnWarning;
    }

    /**
     * @phpstan-assert-if-true !null $this->extensionsDirectory
     */
    public function hasExtensionsDirectory(): bool
    {
        return $this->extensionsDirectory !== null;
    }

    /**
     * @throws Exception
     *
     * @return non-empty-string
     */
    public function extensionsDirectory(): string
    {
        if (!$this->hasExtensionsDirectory()) {
            throw new Exception('Extensions directory is not configured');
        }

        return $this->extensionsDirectory;
    }

    public function beStrictAboutChangesToGlobalState(): bool
    {
        return $this->beStrictAboutChangesToGlobalState;
    }

    public function beStrictAboutOutputDuringTests(): bool
    {
        return $this->beStrictAboutOutputDuringTests;
    }

    public function beStrictAboutTestsThatDoNotTestAnything(): bool
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }

    public function beStrictAboutCoverageMetadata(): bool
    {
        return $this->beStrictAboutCoverageMetadata;
    }

    public function enforceTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }

    public function defaultTimeLimit(): int
    {
        return $this->defaultTimeLimit;
    }

    public function timeoutForSmallTests(): int
    {
        return $this->timeoutForSmallTests;
    }

    public function timeoutForMediumTests(): int
    {
        return $this->timeoutForMediumTests;
    }

    public function timeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }

    /**
     * @phpstan-assert-if-true !null $this->defaultTestSuite
     */
    public function hasDefaultTestSuite(): bool
    {
        return $this->defaultTestSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function defaultTestSuite(): string
    {
        if (!$this->hasDefaultTestSuite()) {
            throw new Exception('Default test suite is not configured');
        }

        return $this->defaultTestSuite;
    }

    public function executionOrder(): int
    {
        return $this->executionOrder;
    }

    public function resolveDependencies(): bool
    {
        return $this->resolveDependencies;
    }

    public function defectsFirst(): bool
    {
        return $this->defectsFirst;
    }

    public function backupGlobals(): bool
    {
        return $this->backupGlobals;
    }

    public function backupStaticProperties(): bool
    {
        return $this->backupStaticProperties;
    }

    public function testdoxPrinter(): bool
    {
        return $this->testdoxPrinter;
    }

    public function testdoxPrinterSummary(): bool
    {
        return $this->testdoxPrinterSummary;
    }

    public function controlGarbageCollector(): bool
    {
        return $this->controlGarbageCollector;
    }

    public function numberOfTestsBeforeGarbageCollection(): int
    {
        return $this->numberOfTestsBeforeGarbageCollection;
    }

    /**
     * @return non-negative-int
     */
    public function shortenArraysForExportThreshold(): int
    {
        return $this->shortenArraysForExportThreshold;
    }
}
