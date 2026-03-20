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
final readonly class Move_Coverage_Directories_To_Source implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $source = $document->get_elements_by_tag_name('source')->item(0);
        if ($source !== null) {
            return;
        }
        $coverage = $document->get_elements_by_tag_name('coverage')->item(0);
        if ($coverage === null) {
            return;
        }
        $root = $document->document_element;
        assert($root instanceof Dom_Element);
        $source = $document->create_element('source');
        $root->append_child($source);
        $xpath = new Domx_Path($document);
        foreach (['include', 'exclude'] as $element) {
            $nodes = $xpath->query('//coverage/' . $element);
            assert($nodes !== false);
            foreach (Snapshot_Node_List::from_node_list($nodes) as $node) {
                $source->append_child($node);
            }
        }
        if ($coverage->child_element_count !== 0) {
            return;
        }
        assert($coverage->parent_node !== null);
        $coverage->parent_node->remove_child($coverage);
    }
}