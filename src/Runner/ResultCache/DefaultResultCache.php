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
namespace Php_Unit\Runner\Result_Cache;

use function array_keys;
use function assert;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_file;
use function json_decode;
use function json_encode;
use const LOCK_EX;
use Php_Unit\Framework\Test_Status\Test_Status;
use Php_Unit\Runner\Directory_Does_Not_Exist_Exception;
use Php_Unit\Runner\Exception;
use Php_Unit\Util\Filesystem;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Default_Result_Cache implements Result_Cache
{
    private const int VERSION = 2;
    private const string DEFAULT_RESULT_CACHE_FILENAME = '.phpunit.result.cache';
    private readonly string $cache_filename;
    /**
     * @var array<string, TestStatus>
     */
    private array $defects = [];
    /**
     * @var array<string, float>
     */
    private array $times = [];
    public function __construct(?string $filepath = null)
    {
        if ($filepath !== null && is_dir($filepath)) {
            $filepath .= DIRECTORY_SEPARATOR . self::DEFAULT_RESULT_CACHE_FILENAME;
        }
        $this->cache_filename = $filepath ?? $_ENV['PHPUNIT_RESULT_CACHE'] ?? self::DEFAULT_RESULT_CACHE_FILENAME;
    }
    public function set_status(Result_Cache_Id $id, Test_Status $status): void
    {
        if ($status->is_success()) {
            return;
        }
        $this->defects[$id->as_string()] = $status;
    }
    public function status(Result_Cache_Id $id): Test_Status
    {
        return $this->defects[$id->as_string()] ?? Test_Status::unknown();
    }
    public function set_time(Result_Cache_Id $id, float $time): void
    {
        $this->times[$id->as_string()] = $time;
    }
    public function time(Result_Cache_Id $id): float
    {
        return $this->times[$id->as_string()] ?? 0.0;
    }
    public function merge_with(self $other): void
    {
        foreach ($other->defects as $id => $defect) {
            $this->defects[$id] = $defect;
        }
        foreach ($other->times as $id => $time) {
            $this->times[$id] = $time;
        }
    }
    public function load(): void
    {
        if (!is_file($this->cache_filename)) {
            return;
        }
        $contents = file_get_contents($this->cache_filename);
        if ($contents === false) {
            return;
        }
        $data = json_decode($contents, true);
        if ($data === null) {
            return;
        }
        if (!isset($data['version'])) {
            return;
        }
        if ($data['version'] !== self::VERSION) {
            return;
        }
        assert(isset($data['defects']) && is_array($data['defects']));
        assert(isset($data['times']) && is_array($data['times']));
        foreach (array_keys($data['defects']) as $test) {
            $data['defects'][$test] = Test_Status::from($data['defects'][$test]);
        }
        $this->defects = $data['defects'];
        $this->times = $data['times'];
    }
    /**
     * @throws Exception
     */
    public function persist(): void
    {
        if (!Filesystem::create_directory(dirname($this->cache_filename))) {
            throw new Directory_Does_Not_Exist_Exception(dirname($this->cache_filename));
        }
        $data = ['version' => self::VERSION, 'defects' => [], 'times' => $this->times];
        foreach ($this->defects as $test => $status) {
            $data['defects'][$test] = $status->as_int();
        }
        file_put_contents($this->cache_filename, json_encode($data), LOCK_EX);
    }
}