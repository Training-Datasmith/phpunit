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
namespace Php_Unit\Event\Test_Runner;

use Php_Unit\Event\Event;
use Php_Unit\Event\Telemetry;
use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Static_Analysis_For_Code_Coverage_Finished implements Event
{
    /**
     * @param non-negative-int $cacheHits
     * @param non-negative-int $cacheMisses
     */
    public function __construct(private Telemetry\Info $telemetry_info, private int $cache_hits, private int $cache_misses)
    {
    }
    public function telemetry_info(): Telemetry\Info
    {
        return $this->telemetry_info;
    }
    /**
     * @return non-negative-int
     */
    public function cache_hits(): int
    {
        return $this->cache_hits;
    }
    /**
     * @return non-negative-int
     */
    public function cache_misses(): int
    {
        return $this->cache_misses;
    }
    /**
     * @return non-empty-string
     */
    public function as_string(): string
    {
        return sprintf('Static Analysis for Code Coverage Finished (%d cache hits, %d cache misses)', $this->cache_hits, $this->cache_misses);
    }
}