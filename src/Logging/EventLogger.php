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
namespace Php_Unit\Logging;

use const FILE_APPEND;
use function file_put_contents;
use function implode;
use const LOCK_EX;
use const PHP_EOL;
use const PHP_OS_FAMILY;
use Php_Unit\Event\Event;
use Php_Unit\Event\Tracer\Tracer;
use function preg_split;
use function str_repeat;
use function strlen;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Event_Logger implements Tracer
{
    public function __construct(private string $path, private bool $include_telemetry_info)
    {
    }
    public function trace(Event $event): void
    {
        $telemetry_info = $this->telemetry_info($event);
        $indentation = PHP_EOL . str_repeat(' ', strlen($telemetry_info));
        $flags = FILE_APPEND;
        if (!(PHP_OS_FAMILY === 'Windows' || PHP_OS_FAMILY === 'Darwin') || $this->path !== 'php://stdout') {
            $flags |= LOCK_EX;
        }
        $lines = preg_split('/\r\n|\r|\n/', $event->as_string());
        if ($lines === false) {
            $lines = [];
        }
        file_put_contents($this->path, $telemetry_info . implode($indentation, $lines) . PHP_EOL, $flags);
    }
    private function telemetry_info(Event $event): string
    {
        if (!$this->include_telemetry_info) {
            return '';
        }
        return $event->telemetry_info()->as_string() . ' ';
    }
}