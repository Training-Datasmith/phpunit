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

use function assert;
use function defined;
use function fgets;
use function file_put_contents;
use function getcwd;
use function is_file;
use const PHP_EOL;
use Php_Unit\Runner\Version;
use Php_Unit\Text_Ui\Xml_Configuration\Generator;
use function sprintf;
use const STDIN;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Generate_Configuration_Command implements Command
{
    public function execute(): Result
    {
        $directory = getcwd();
        print 'Generating phpunit.xml in ' . $directory . PHP_EOL . PHP_EOL;
        print 'Bootstrap script (relative to path shown above; default: vendor/autoload.php): ';
        $bootstrap_script = $this->read();
        print 'Tests directory (relative to path shown above; default: tests): ';
        $tests_directory = $this->read();
        print 'Source directory (relative to path shown above; default: src): ';
        $src = $this->read();
        print 'Cache directory (relative to path shown above; default: .phpunit.cache): ';
        $cache_directory = $this->read();
        if ($bootstrap_script === '') {
            $bootstrap_script = 'vendor/autoload.php';
        }
        if ($tests_directory === '') {
            $tests_directory = 'tests';
        }
        if ($src === '') {
            $src = 'src';
        }
        if ($cache_directory === '') {
            $cache_directory = '.phpunit.cache';
        }
        if (defined('PHPUNIT_COMPOSER_INSTALL') && is_file($directory . '/vendor/phpunit/phpunit/phpunit.xsd')) {
            $schema_location = 'vendor/phpunit/phpunit/phpunit.xsd';
        } else {
            $schema_location = sprintf('https://schema.phpunit.de/%s/phpunit.xsd', Version::series());
        }
        $generator = new Generator();
        $result = @file_put_contents($directory . '/phpunit.xml', $generator->generate_default_configuration($schema_location, $bootstrap_script, $tests_directory, $src, $cache_directory));
        if ($result !== false) {
            return Result::from(sprintf(PHP_EOL . 'Generated phpunit.xml in %s.' . PHP_EOL . 'Make sure to exclude the %s directory from version control.' . PHP_EOL, $directory, $cache_directory));
        }
        // @codeCoverageIgnoreStart
        return Result::from(sprintf(PHP_EOL . 'Could not write phpunit.xml in %s.' . PHP_EOL, $directory), Result::EXCEPTION);
        // @codeCoverageIgnoreEnd
    }
    private function read(): string
    {
        $buffer = fgets(STDIN);
        assert($buffer !== false);
        return trim($buffer);
    }
}