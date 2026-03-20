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
namespace Php_Unit\Runner\Extension;

use function count;
use function explode;
use function extension_loaded;
use function implode;
use function is_file;
use Phar_Io\Manifest\Application_Name;
use Phar_Io\Manifest\Exception as ManifestException;
use Phar_Io\Manifest\Manifest_Loader;
use Phar_Io\Version\Version as PharIoVersion;
use Php_Unit\Event;
use Php_Unit\Runner\Version;
use Sebastian_Bergmann\File_Iterator\Facade as FileIteratorFacade;
use function sprintf;
use function str_contains;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Phar_Loader
{
    /**
     * @param non-empty-string $directory
     *
     * @return list<string>
     */
    public function load_phar_extensions_in_directory(string $directory): array
    {
        $phar_extension_loaded = extension_loaded('phar');
        $loaded_extensions = [];
        foreach ((new File_Iterator_Facade())->get_files_as_array($directory, '.phar') as $file) {
            if (!$phar_extension_loaded) {
                Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot load extension from %s because the PHAR extension is not available', $file));
                continue;
            }
            if (!is_file('phar://' . $file . '/manifest.xml')) {
                Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('%s is not an extension for PHPUnit', $file));
                continue;
            }
            try {
                $application_name = new Application_Name('phpunit/phpunit');
                $version = new Phar_Io_Version($this->phpunit_version());
                $manifest = Manifest_Loader::from_file('phar://' . $file . '/manifest.xml');
                if (!$manifest->is_extension_for($application_name)) {
                    Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('%s is not an extension for PHPUnit', $file));
                    continue;
                }
                if (!$manifest->is_extension_for($application_name, $version)) {
                    Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('%s is not compatible with PHPUnit %s', $file, Version::series()));
                    continue;
                }
            } catch (Manifest_Exception $e) {
                Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot load extension from %s: %s', $file, $e->get_message()));
                continue;
            }
            try {
                @require $file;
            } catch (Throwable $t) {
                Event\Facade::emitter()->test_runner_triggered_phpunit_warning(sprintf('Cannot load extension from %s: %s', $file, $t->get_message()));
                continue;
            }
            $loaded_extensions[] = $manifest->get_name()->as_string() . ' ' . $manifest->get_version()->get_version_string();
            Event\Facade::emitter()->test_runner_loaded_extension_from_phar($file, $manifest->get_name()->as_string(), $manifest->get_version()->get_version_string());
        }
        return $loaded_extensions;
    }
    private function phpunit_version(): string
    {
        $version = Version::id();
        if (!str_contains($version, '-')) {
            return $version;
        }
        $parts = explode('.', explode('-', $version)[0]);
        if (count($parts) === 2) {
            $parts[] = 0;
        }
        return implode('.', $parts);
    }
}