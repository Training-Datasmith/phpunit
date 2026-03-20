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
namespace Php_Unit\Util\PHP;

use function assert;
use function file_get_contents;
use function is_file;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Framework\Child_Process_Result_Processor;
use Php_Unit\Framework\Test;
use function unlink;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Job_Runner
{
    public function __construct(private Child_Process_Result_Processor $processor)
    {
    }
    /**
     * @param non-empty-string $processResultFile
     */
    final public function run_test_job(Job $job, string $process_result_file, Test $test): void
    {
        $result = $this->run($job);
        $process_result = '';
        if (is_file($process_result_file)) {
            $process_result = file_get_contents($process_result_file);
            assert($process_result !== false);
            @unlink($process_result_file);
        }
        $this->processor->process($test, $process_result, $result->stderr());
        Event_Facade::emitter()->child_process_finished($result->stdout(), $result->stderr());
    }
    abstract public function run(Job $job): Result;
}