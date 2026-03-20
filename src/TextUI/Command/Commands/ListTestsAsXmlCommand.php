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
namespace Php_Unit\Text_Ui\Command;

use function assert;
use function file_put_contents;
use function ksort;
use const PHP_EOL;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use ReflectionClass;
use function sprintf;
use Xml_Writer;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class List_Tests_As_Xml_Command implements Command
{
    /**
     * @param list<PhptTestCase|TestCase> $tests
     */
    public function __construct(private array $tests, private string $filename)
    {
    }
    public function execute(): Result
    {
        $writer = new Xml_Writer();
        $writer->open_memory();
        $writer->set_indent(true);
        $writer->start_document();
        $writer->start_element('testSuite');
        $writer->write_attribute('xmlns', 'https://xml.phpunit.de/testSuite');
        $writer->start_element('tests');
        $current_test_class = null;
        $groups = [];
        foreach ($this->tests as $test) {
            if ($test instanceof Test_Case) {
                foreach ($test->groups() as $group) {
                    if (!isset($groups[$group])) {
                        $groups[$group] = [];
                    }
                    $groups[$group][] = $test->value_object_for_events()->id();
                }
                if ($test::class !== $current_test_class) {
                    if ($current_test_class !== null) {
                        $writer->end_element();
                    }
                    $file = (new ReflectionClass($test))->get_file_name();
                    assert($file !== false);
                    $writer->start_element('testClass');
                    $writer->write_attribute('name', $test::class);
                    $writer->write_attribute('file', $file);
                    $current_test_class = $test::class;
                }
                $writer->start_element('testMethod');
                $writer->write_attribute('id', $test->value_object_for_events()->id());
                $writer->write_attribute('name', $test->value_object_for_events()->method_name());
                $writer->end_element();
                continue;
            }
            if ($current_test_class !== null) {
                $writer->end_element();
                $current_test_class = null;
            }
            $writer->start_element('phpt');
            $writer->write_attribute('file', $test->get_name());
            $writer->end_element();
        }
        if ($current_test_class !== null) {
            $writer->end_element();
        }
        $writer->end_element();
        ksort($groups);
        $writer->start_element('groups');
        foreach ($groups as $group_name => $test_ids) {
            $writer->start_element('group');
            $writer->write_attribute('name', (string) $group_name);
            foreach ($test_ids as $test_id) {
                $writer->start_element('test');
                $writer->write_attribute('id', $test_id);
                $writer->end_element();
            }
            $writer->end_element();
        }
        $writer->end_element();
        $writer->end_element();
        file_put_contents($this->filename, $writer->output_memory());
        return Result::from(sprintf('Wrote list of tests that would have been run to %s' . PHP_EOL, $this->filename));
    }
}