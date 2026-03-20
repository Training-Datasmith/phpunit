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
namespace Php_Unit\Util;

use function array_any;
use function assert;
use function class_exists;
use Composer\Autoload\Class_Loader;
use Deep_Copy\Deep_Copy;
use function defined;
use function dirname;
use function is_dir;
use Phar_Io\Manifest\Manifest;
use Phar_Io\Version\Version as PharIoVersion;
use const PHP_OS_FAMILY;
use Php_Parser\Parser;
use Php_Unit\Framework\Test_Case;
use function realpath;
use ReflectionClass;
use Sebastian_Bergmann\Cli_Parser\Parser as CliParser;
use Sebastian_Bergmann\Code_Coverage\Code_Coverage;
use Sebastian_Bergmann\Comparator\Comparator;
use Sebastian_Bergmann\Complexity\Calculator;
use Sebastian_Bergmann\Diff\Diff;
use Sebastian_Bergmann\Environment\Runtime;
use Sebastian_Bergmann\Exporter\Exporter;
use Sebastian_Bergmann\File_Iterator\Facade as FileIteratorFacade;
use Sebastian_Bergmann\Global_State\Snapshot;
use Sebastian_Bergmann\Invoker\Invoker;
use Sebastian_Bergmann\Lines_Of_Code\Counter;
use Sebastian_Bergmann\Object_Enumerator\Enumerator;
use Sebastian_Bergmann\Object_Reflector\Object_Reflector;
use Sebastian_Bergmann\Recursion_Context\Context;
use Sebastian_Bergmann\Template\Template;
use Sebastian_Bergmann\Timer\Timer;
use Sebastian_Bergmann\Type\Type_Name;
use Sebastian_Bergmann\Version;
use staabm\Side_Effects_Detector\Side_Effects_Detector;
use function str_starts_with;
use function sys_get_temp_dir;
use The_Seer\Tokenizer\Tokenizer;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Exclude_List
{
    /**
     * @var non-empty-array<class-string, positive-int>
     */
    private const array EXCLUDED_CLASS_NAMES = [
        // composer
        Class_Loader::class => 1,
        // myclabs/deepcopy
        Deep_Copy::class => 1,
        // nikic/php-parser
        Parser::class => 1,
        // phar-io/manifest
        Manifest::class => 1,
        // phar-io/version
        Phar_Io_Version::class => 1,
        // phpunit/phpunit
        Test_Case::class => 2,
        // phpunit/php-code-coverage
        Code_Coverage::class => 1,
        // phpunit/php-file-iterator
        File_Iterator_Facade::class => 1,
        // phpunit/php-invoker
        Invoker::class => 1,
        // phpunit/php-text-template
        Template::class => 1,
        // phpunit/php-timer
        Timer::class => 1,
        // sebastian/cli-parser
        Cli_Parser::class => 1,
        // sebastian/comparator
        Comparator::class => 1,
        // sebastian/complexity
        Calculator::class => 1,
        // sebastian/diff
        Diff::class => 1,
        // sebastian/environment
        Runtime::class => 1,
        // sebastian/exporter
        Exporter::class => 1,
        // sebastian/global-state
        Snapshot::class => 1,
        // sebastian/lines-of-code
        Counter::class => 1,
        // sebastian/object-enumerator
        Enumerator::class => 1,
        // sebastian/object-reflector
        Object_Reflector::class => 1,
        // sebastian/recursion-context
        Context::class => 1,
        // sebastian/type
        Type_Name::class => 1,
        // sebastian/version
        Version::class => 1,
        // staabm/side-effects-detector
        Side_Effects_Detector::class => 1,
        // theseer/tokenizer
        Tokenizer::class => 1,
    ];
    /**
     * @var list<string>
     */
    private static array $directories = [];
    private static bool $initialized = false;
    private readonly bool $enabled;
    /**
     * @param non-empty-string $directory
     *
     * @throws InvalidDirectoryException
     */
    public static function add_directory(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new Invalid_Directory_Exception($directory);
        }
        $directory = realpath($directory);
        assert($directory !== false);
        self::$directories[] = $directory;
    }
    public function __construct(?bool $enabled = null)
    {
        if ($enabled === null) {
            $enabled = !defined('PHPUNIT_TESTSUITE');
        }
        $this->enabled = $enabled;
    }
    /**
     * @return list<string>
     */
    public function get_excluded_directories(): array
    {
        self::initialize();
        return self::$directories;
    }
    public function is_excluded(string $file): bool
    {
        if (!$this->enabled) {
            return false;
        }
        self::initialize();
        return array_any(self::$directories, static fn(string $directory): bool => str_starts_with($file, $directory));
    }
    private static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }
        foreach (self::EXCLUDED_CLASS_NAMES as $class_name => $parent) {
            if (!class_exists($class_name)) {
                continue;
            }
            $directory = (new ReflectionClass($class_name))->get_file_name();
            for ($i = 0; $i < $parent; $i++) {
                $directory = dirname($directory);
            }
            self::$directories[] = $directory;
        }
        /**
         * Hide process isolation workaround on Windows:
         * tempnam() prefix is limited to first 3 characters.
         *
         * @see https://php.net/manual/en/function.tempnam.php
         */
        if (PHP_OS_FAMILY === 'Windows') {
            // @codeCoverageIgnoreStart
            self::$directories[] = sys_get_temp_dir() . '\PHP';
            // @codeCoverageIgnoreEnd
        }
        self::$initialized = true;
    }
}