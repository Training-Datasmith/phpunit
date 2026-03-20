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
namespace Php_Unit\Text_Ui\Command;

use const PHP_EOL;
use Php_Unit\Text_Ui\Configuration\Code_Coverage_Filter_Registry;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Configuration\No_Coverage_Cache_Directory_Exception;
use function printf;
use Sebastian_Bergmann\Code_Coverage\Static_Analysis\Cache_Warmer;
use Sebastian_Bergmann\Timer\No_Active_Timer_Exception;
use Sebastian_Bergmann\Timer\Timer;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final readonly class Warm_Code_Coverage_Cache_Command implements Command
{
    public function __construct(private Configuration $configuration, private Code_Coverage_Filter_Registry $code_coverage_filter_registry)
    {
    }
    /**
     * @throws NoActiveTimerException
     * @throws NoCoverageCacheDirectoryException
     */
    public function execute(): Result
    {
        if (!$this->configuration->has_coverage_cache_directory()) {
            return Result::from('Cache for static analysis has not been configured' . PHP_EOL, Result::FAILURE);
        }
        $this->code_coverage_filter_registry->init($this->configuration, true);
        if (!$this->code_coverage_filter_registry->configured()) {
            return Result::from('Filter for code coverage has not been configured' . PHP_EOL, Result::FAILURE);
        }
        $timer = new Timer();
        $timer->start();
        print 'Warming cache for static analysis ... ';
        /** @phpstan-ignore new.internalClass,method.internalClass */
        $statistics = (new Cache_Warmer())->warm_cache($this->configuration->coverage_cache_directory(), !$this->configuration->disable_code_coverage_ignore(), $this->configuration->ignore_deprecated_code_units_from_code_coverage(), $this->code_coverage_filter_registry->get());
        printf('[%s]%s%s%d file%s processed, %d cache hit%s, %d cache miss%s%s', $timer->stop()->as_string(), PHP_EOL, PHP_EOL, $statistics['cacheHits'] + $statistics['cacheMisses'], $statistics['cacheHits'] + $statistics['cacheMisses'] !== 1 ? 's' : '', $statistics['cacheHits'], $statistics['cacheHits'] !== 1 ? 's' : '', $statistics['cacheMisses'], $statistics['cacheMisses'] !== 1 ? 'es' : '', PHP_EOL);
        return Result::from();
    }
}