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
use function defined;
use function dirname;
use function file_put_contents;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use Sebastian_Bergmann\Template\InvalidArgumentException;
use Sebastian_Bergmann\Template\Template;
use function str_replace;
use function var_export;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see https://qa.php.net/phpt_details.php
 */
final readonly class Renderer
{
    /**
     * @param non-empty-string $phptFile
     * @param non-empty-string $code
     *
     * @return non-empty-string
     */
    public function render(string $phpt_file, string $code): string
    {
        return str_replace(['__DIR__', '__FILE__'], ["'" . dirname($phpt_file) . "'", "'" . $phpt_file . "'"], $code);
    }
    /**
     * @param non-empty-string                                         $job
     * @param array{coverage: non-empty-string, job: non-empty-string} $files
     *
     * @param-out non-empty-string $job
     *
     * @throws InvalidArgumentException
     */
    public function render_for_coverage(string &$job, bool $path_coverage, ?string $code_coverage_cache_directory, array $files): void
    {
        $template = new Template(__DIR__ . '/templates/phpt.tpl');
        $composer_autoload = '\'\'';
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composer_autoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        }
        $phar = '\'\'';
        if (defined('__PHPUNIT_PHAR__')) {
            $phar = var_export(__PHPUNIT_PHAR__, true);
        }
        if ($code_coverage_cache_directory === null) {
            $code_coverage_cache_directory = 'null';
        } else {
            $code_coverage_cache_directory = "'" . $code_coverage_cache_directory . "'";
        }
        $bootstrap = '';
        if (Configuration_Registry::get()->has_bootstrap()) {
            $bootstrap = Configuration_Registry::get()->bootstrap();
        }
        $template->set_var(['bootstrap' => $bootstrap, 'composerAutoload' => $composer_autoload, 'phar' => $phar, 'job' => $files['job'], 'coverageFile' => $files['coverage'], 'driverMethod' => $path_coverage ? 'forLineAndPathCoverage' : 'forLineCoverage', 'codeCoverageCacheDirectory' => $code_coverage_cache_directory]);
        file_put_contents($files['job'], $job);
        $rendered = $template->render();
        assert($rendered !== '');
        $job = $rendered;
    }
}