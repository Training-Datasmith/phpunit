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
use function in_array;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Move_Whitelist_Excludes_To_Coverage implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $whitelist = $document->get_elements_by_tag_name('whitelist')->item(0);
        if ($whitelist === null) {
            return;
        }
        $exclude_nodes = Snapshot_Node_List::from_node_list($whitelist->get_elements_by_tag_name('exclude'));
        if ($exclude_nodes->count() === 0) {
            return;
        }
        $coverage = $document->get_elements_by_tag_name('coverage')->item(0);
        if (!$coverage instanceof Dom_Element) {
            throw new Migration_Exception('Unexpected state - No coverage element');
        }
        $target_exclude = $coverage->get_elements_by_tag_name('exclude')->item(0);
        if ($target_exclude === null) {
            $target_exclude = $coverage->append_child($document->create_element('exclude'));
        }
        foreach ($exclude_nodes as $exclude_node) {
            assert($exclude_node instanceof Dom_Element);
            foreach (Snapshot_Node_List::from_node_list($exclude_node->child_nodes) as $child) {
                if (!$child instanceof Dom_Element) {
                    continue;
                }
                if (!in_array($child->node_name, ['directory', 'file'], true)) {
                    continue;
                }
                $target_exclude->append_child($child);
            }
            if ($exclude_node->get_elements_by_tag_name('*')->count() !== 0) {
                throw new Migration_Exception('Dangling child elements in exclude found.');
            }
            $whitelist->remove_child($exclude_node);
        }
    }
}