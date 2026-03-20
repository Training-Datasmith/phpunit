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

use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function preg_match;
use function realpath;
use Sebastian_Bergmann\File_Iterator\Facade as FileIteratorFacade;
use function serialize;
use Spl_Object_Storage;
use function str_replace;
use function unserialize;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Source_Mapper
{
    /**
     * @var ?SplObjectStorage<Source, array<non-empty-string, true>>
     */
    private static ?Spl_Object_Storage $files = null;
    public static function save_to(string $path, Source $source): bool
    {
        $map = (new self())->map($source);
        return file_put_contents($path, serialize($map)) !== false;
    }
    /**
     * @codeCoverageIgnore
     */
    public static function load_from(string $path, Source $source): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }
        $map = unserialize($content, ['allowed_classes' => false]);
        if (!is_array($map)) {
            return;
        }
        if (self::$files === null) {
            self::$files = new Spl_Object_Storage();
        }
        /** @phpstan-ignore offsetAssign.valueType */
        self::$files[$source] = $map;
    }
    /**
     * @return array<non-empty-string, true>
     */
    public function map(Source $source): array
    {
        if (self::$files === null) {
            self::$files = new Spl_Object_Storage();
        }
        if (isset(self::$files[$source])) {
            return self::$files[$source];
        }
        $files = [];
        $directories = $this->aggregate_directories($source->include_directories());
        foreach ($directories as $path => [$prefixes, $suffixes]) {
            $base_path = realpath($path);
            foreach ((new File_Iterator_Facade())->get_files_as_array($path, $suffixes, $prefixes) as $file) {
                $file = realpath($file);
                if (!$file) {
                    continue;
                }
                if ($this->is_in_hidden_directory($file, $base_path)) {
                    continue;
                }
                $files[$file] = true;
            }
        }
        foreach ($source->include_files() as $file) {
            $file = realpath($file->path());
            if (!$file) {
                continue;
            }
            $files[$file] = true;
        }
        $directories = $this->aggregate_directories($source->exclude_directories());
        foreach ($directories as $path => [$prefixes, $suffixes]) {
            foreach ((new File_Iterator_Facade())->get_files_as_array($path, $suffixes, $prefixes) as $file) {
                $file = realpath($file);
                if (!$file) {
                    continue;
                }
                if (!isset($files[$file])) {
                    continue;
                }
                unset($files[$file]);
            }
        }
        foreach ($source->exclude_files() as $file) {
            $file = realpath($file->path());
            if (!$file) {
                continue;
            }
            if (!isset($files[$file])) {
                continue;
            }
            unset($files[$file]);
        }
        self::$files[$source] = $files;
        return $files;
    }
    /**
     * @return array<non-empty-string, true>
     */
    public function map_for_code_coverage(Source $source): array
    {
        $files = $this->map($source);
        foreach ($source->include_directories() as $directory) {
            if ($directory->include_in_code_coverage()) {
                continue;
            }
            foreach ((new File_Iterator_Facade())->get_files_as_array($directory->path(), $directory->suffix(), $directory->prefix()) as $file) {
                $file = realpath($file);
                if (!$file) {
                    continue;
                }
                unset($files[$file]);
            }
        }
        foreach ($source->include_files() as $file) {
            if ($file->include_in_code_coverage()) {
                continue;
            }
            $path = realpath($file->path());
            if (!$path) {
                continue;
            }
            unset($files[$path]);
        }
        return $files;
    }
    private function is_in_hidden_directory(string $path, false|string $base_path): bool
    {
        $relative_path = str_replace((string) $base_path, '', $path);
        $separator = DIRECTORY_SEPARATOR === '\\' ? '\\\\' : '/';
        return preg_match('=' . $separator . '\.[^' . $separator . ']*' . $separator . '=', $relative_path) === 1;
    }
    /**
     * @return array<string,array{list<string>,list<string>}>
     */
    private function aggregate_directories(Filter_Directory_Collection $directories): array
    {
        $aggregated = [];
        foreach ($directories as $directory) {
            if (!isset($aggregated[$directory->path()])) {
                $aggregated[$directory->path()] = [0 => [], 1 => []];
            }
            $prefix = $directory->prefix();
            if ($prefix !== '') {
                $aggregated[$directory->path()][0][] = $prefix;
            }
            $suffix = $directory->suffix();
            if ($suffix !== '') {
                $aggregated[$directory->path()][1][] = $suffix;
            }
        }
        return $aggregated;
    }
}