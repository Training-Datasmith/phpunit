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
namespace Php_Unit\Util\Xml;

use Dom_Document;
use function error_reporting;
use function file_get_contents;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function sprintf;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Loader
{
    /**
     * @throws XmlException
     */
    public function load_file(string $filename): Dom_Document
    {
        $reporting = error_reporting(0);
        $contents = file_get_contents($filename);
        error_reporting($reporting);
        if ($contents === false) {
            throw new Xml_Exception(sprintf('Could not read XML from file "%s"', $filename));
        }
        if (trim($contents) === '') {
            throw new Xml_Exception(sprintf('Could not parse XML from empty file "%s"', $filename));
        }
        return $this->load($contents);
    }
    /**
     * @throws XmlException
     */
    public function load(string $actual): Dom_Document
    {
        if ($actual === '') {
            throw new Xml_Exception('Could not parse XML from empty string');
        }
        $document = new Dom_Document();
        $document->preserve_white_space = false;
        $internal = libxml_use_internal_errors(true);
        $message = '';
        $reporting = error_reporting(0);
        $loaded = $document->load_xml($actual);
        foreach (libxml_get_errors() as $error) {
            $message .= "\n" . $error->message;
        }
        libxml_use_internal_errors($internal);
        error_reporting($reporting);
        if ($loaded === false) {
            if ($message === '') {
                // @codeCoverageIgnoreStart
                $message = 'Could not load XML for unknown reason';
                // @codeCoverageIgnoreEnd
            }
            throw new Xml_Exception($message);
        }
        return $document;
    }
}