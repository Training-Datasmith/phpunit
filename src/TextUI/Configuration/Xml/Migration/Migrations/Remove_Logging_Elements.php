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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use function assert;
use Dom_Document;
use Dom_Element;
use Domx_Path;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Remove_Logging_Elements implements Migration
{
    public function migrate(Dom_Document $document): void
    {
        $this->remove_test_dox_element($document);
        $this->remove_text_element($document);
    }
    private function remove_test_dox_element(Dom_Document $document): void
    {
        $nodes = (new Domx_Path($document))->query('logging/testdoxXml');
        assert($nodes !== false);
        $node = $nodes->item(0);
        if (!$node instanceof Dom_Element || $node->parent_node === null) {
            return;
        }
        $node->parent_node->remove_child($node);
    }
    private function remove_text_element(Dom_Document $document): void
    {
        $nodes = (new Domx_Path($document))->query('logging/text');
        assert($nodes !== false);
        $node = $nodes->item(0);
        if (!$node instanceof Dom_Element || $node->parent_node === null) {
            return;
        }
        $node->parent_node->remove_child($node);
    }
}