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

use function assert;
use const DIRECTORY_SEPARATOR;
use function dirname;
use Dom_Element;
use Domx_Path;
use function is_file;
use Php_Unit\Util\Xml\Loader as XmlLoader;
use Php_Unit\Util\Xml\Xml_Exception;
use function realpath;
use function sprintf;
use function str_replace;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Reader
{
    /**
     * @param non-empty-string $baselineFile
     *
     * @throws CannotLoadBaselineException
     */
    public function read(string $baseline_file): Baseline
    {
        if (!is_file($baseline_file)) {
            throw new Cannot_Load_Baseline_Exception(sprintf('Cannot read baseline %s, file does not exist', $baseline_file));
        }
        try {
            $document = (new Xml_Loader())->load_file($baseline_file);
        } catch (Xml_Exception $e) {
            throw new Cannot_Load_Baseline_Exception(sprintf('Cannot read baseline %s: %s', $baseline_file, trim($e->get_message())));
        }
        $version = (int) $document->document_element->get_attribute('version');
        if ($version !== Baseline::VERSION) {
            throw new Cannot_Load_Baseline_Exception(sprintf('Cannot read baseline %s, version %d is not supported', $baseline_file, $version));
        }
        $baseline = new Baseline();
        $baseline_directory = dirname(realpath($baseline_file));
        $xpath = new Domx_Path($document);
        foreach ($xpath->query('file') as $file_element) {
            assert($file_element instanceof Dom_Element);
            $file = $baseline_directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file_element->get_attribute('path'));
            foreach ($xpath->query('line', $file_element) as $line_element) {
                assert($line_element instanceof Dom_Element);
                $line = (int) $line_element->get_attribute('number');
                $hash = $line_element->get_attribute('hash');
                foreach ($xpath->query('issue', $line_element) as $issue_element) {
                    assert($issue_element instanceof Dom_Element);
                    $description = $issue_element->text_content;
                    assert($line > 0);
                    assert($hash !== '');
                    assert($description !== '');
                    $baseline->add(Issue::from($file, $line, $hash, $description));
                }
            }
        }
        return $baseline;
    }
}