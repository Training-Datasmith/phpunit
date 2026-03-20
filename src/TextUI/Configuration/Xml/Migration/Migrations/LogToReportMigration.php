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
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Log_To_Report_Migration implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $coverage = $document->get_elements_by_tag_name('coverage')->item(0);
        if (!$coverage instanceof Dom_Element) {
            throw new Migration_Exception('Unexpected state - No coverage element');
        }
        $log_node = $this->find_log_node($document);
        if ($log_node === null) {
            return;
        }
        $report_child = $this->to_report_format($log_node);
        $report = $coverage->get_elements_by_tag_name('report')->item(0);
        if ($report === null) {
            $report = $coverage->append_child($document->create_element('report'));
        }
        $report->append_child($report_child);
        $log_node->parent_node->remove_child($log_node);
    }
    /**
     * @param list<non-empty-string> $attributes
     */
    protected function migrate_attributes(Dom_Element $src, Dom_Element $dest, array $attributes): void
    {
        foreach ($attributes as $attr) {
            if (!$src->has_attribute($attr)) {
                continue;
            }
            $dest->set_attribute($attr, $src->get_attribute($attr));
            $src->remove_attribute($attr);
        }
    }
    abstract protected function for_type(): string;
    abstract protected function to_report_format(Dom_Element $log_node): Dom_Element;
    private function find_log_node(Dom_Document $document): ?Dom_Element
    {
        $xpath = new Domx_Path($document);
        $log_node = $xpath->query(sprintf('//logging/log[@type="%s"]', $this->for_type()));
        assert($log_node !== false);
        $log_node = $log_node->item(0);
        if (!$log_node instanceof Dom_Element) {
            return null;
        }
        return $log_node;
    }
}