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
namespace Php_Unit\Runner\Phpt;

use function assert;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function explode;
use function file;
use function file_get_contents;
use function is_file;
use function is_readable;
use function is_string;
use Php_Unit\Runner\Exception;
use function preg_match;
use function rtrim;
use function str_contains;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see https://qa.php.net/phpt_details.php
 */
final readonly class Parser
{
    /**
     * @param non-empty-string $phptFile
     *
     * @throws Exception
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function parse(string $phpt_file): array
    {
        $sections = [];
        $section = '';
        $unsupported_sections = ['CGI', 'COOKIE', 'DEFLATE_POST', 'EXPECTHEADERS', 'EXTENSIONS', 'GET', 'GZIP_POST', 'HEADERS', 'PHPDBG', 'POST', 'POST_RAW', 'PUT', 'REDIRECTTEST', 'REQUEST'];
        $line_nr = 0;
        foreach (file($phpt_file) as $line) {
            $line_nr++;
            if (preg_match('/^--([_A-Z]+)--/', $line, $result)) {
                $section = $result[1];
                $sections[$section] = '';
                $sections[$section . '_offset'] = $line_nr;
                continue;
            }
            if ($section === '') {
                throw new Invalid_Phpt_File_Exception();
            }
            $sections[$section] .= $line;
        }
        if (isset($sections['FILEEOF'])) {
            $sections['FILE'] = rtrim((string) $sections['FILEEOF'], "\r\n");
            unset($sections['FILEEOF']);
        }
        $this->parse_external($phpt_file, $sections);
        $this->validate($sections);
        foreach ($unsupported_sections as $unsupported_section) {
            if (isset($sections[$unsupported_section])) {
                throw new Unsupported_Phpt_Section_Exception($unsupported_section);
            }
        }
        return $sections;
    }
    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function parse_env_section(string $content): array
    {
        $env = [];
        foreach (explode("\n", trim($content)) as $e) {
            $e = explode('=', trim($e), 2);
            if ($e[0] !== '' && isset($e[1])) {
                $env[$e[0]] = $e[1];
            }
        }
        return $env;
    }
    /**
     * @param array<string>|string                                              $content
     * @param array<non-empty-string, array<non-empty-string>|non-empty-string> $ini
     *
     * @return array<non-empty-string, array<non-empty-string>|non-empty-string>
     */
    public function parse_ini_section(array|string $content, array $ini = []): array
    {
        if (is_string($content)) {
            $content = explode("\n", trim($content));
        }
        foreach ($content as $setting) {
            if (!str_contains($setting, '=')) {
                continue;
            }
            $setting = explode('=', $setting, 2);
            $name = trim($setting[0]);
            $value = trim($setting[1]);
            if ($name === 'extension' || $name === 'zend_extension') {
                if (!isset($ini[$name])) {
                    $ini[$name] = [];
                }
                $ini[$name][] = $value;
                continue;
            }
            $ini[$name] = $value;
        }
        return $ini;
    }
    /**
     * @param non-empty-string                          $phptFile
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @throws Exception
     */
    private function parse_external(string $phpt_file, array &$sections): void
    {
        $allow_sections = ['FILE', 'EXPECT', 'EXPECTF', 'EXPECTREGEX'];
        $test_directory = dirname($phpt_file) . DIRECTORY_SEPARATOR;
        foreach ($allow_sections as $section) {
            if (isset($sections[$section . '_EXTERNAL'])) {
                $external_filename = trim($sections[$section . '_EXTERNAL']);
                if (!is_file($test_directory . $external_filename) || !is_readable($test_directory . $external_filename)) {
                    throw new Phpt_External_File_Cannot_Be_Loaded_Exception($section, $test_directory . $external_filename);
                }
                $contents = file_get_contents($test_directory . $external_filename);
                assert($contents !== false && $contents !== '');
                $sections[$section] = $contents;
            }
        }
    }
    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @throws InvalidPhptFileException
     */
    private function validate(array $sections): void
    {
        if (!isset($sections['FILE'])) {
            throw new Invalid_Phpt_File_Exception();
        }
        if (!isset($sections['EXPECT']) && !isset($sections['EXPECTF']) && !isset($sections['EXPECTREGEX'])) {
            throw new Invalid_Phpt_File_Exception();
        }
    }
}