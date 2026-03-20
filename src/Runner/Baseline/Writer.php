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
namespace Php_Unit\Runner\Baseline;

use function dirname;
use function file_put_contents;
use function is_dir;
use function realpath;
use function sprintf;
use Xml_Writer;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Writer
{
    /**
     * @param non-empty-string $baselineFile
     *
     * @throws CannotWriteBaselineException
     */
    public function write(string $baseline_file, Baseline $baseline): void
    {
        $normalized_baseline_file = realpath(dirname($baseline_file));
        if ($normalized_baseline_file === false || !is_dir($normalized_baseline_file)) {
            throw new Cannot_Write_Baseline_Exception(sprintf('Cannot write baseline to "%s".', $baseline_file));
        }
        $path_calculator = new Relative_Path_Calculator($normalized_baseline_file);
        $writer = new Xml_Writer();
        $writer->open_memory();
        $writer->set_indent(true);
        $writer->start_document();
        $writer->start_element('files');
        $writer->write_attribute('version', (string) Baseline::VERSION);
        foreach ($baseline->grouped_by_file_and_line() as $file => $lines) {
            $writer->start_element('file');
            $writer->write_attribute('path', $path_calculator->calculate($file));
            foreach ($lines as $line => $issues) {
                $writer->start_element('line');
                $writer->write_attribute('number', (string) $line);
                $writer->write_attribute('hash', $issues[0]->hash());
                foreach ($issues as $issue) {
                    $writer->start_element('issue');
                    $writer->write_cdata($issue->description());
                    $writer->end_element();
                }
                $writer->end_element();
            }
            $writer->end_element();
        }
        $writer->end_element();
        file_put_contents($baseline_file, $writer->output_memory());
    }
}