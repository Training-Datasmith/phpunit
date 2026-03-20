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
namespace Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage;

use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Clover;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Cobertura;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Crap4j;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Html;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Open_Clover;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Php;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Text;
use Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report\Xml;
use Php_Unit\Text_Ui\Xml_Configuration\Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Code_Coverage
{
    public function __construct(private bool $path_coverage, private bool $include_uncovered_files, private bool $ignore_deprecated_code_units, private bool $disable_code_coverage_ignore, private ?Clover $clover, private ?Cobertura $cobertura, private ?Crap4j $crap4j, private ?Html $html, private ?Open_Clover $open_clover, private ?Php $php, private ?Text $text, private ?Xml $xml)
    {
    }
    public function path_coverage(): bool
    {
        return $this->path_coverage;
    }
    public function include_uncovered_files(): bool
    {
        return $this->include_uncovered_files;
    }
    public function ignore_deprecated_code_units(): bool
    {
        return $this->ignore_deprecated_code_units;
    }
    public function disable_code_coverage_ignore(): bool
    {
        return $this->disable_code_coverage_ignore;
    }
    /**
     * @phpstan-assert-if-true !null $this->clover
     */
    public function has_clover(): bool
    {
        return $this->clover !== null;
    }
    /**
     * @throws Exception
     */
    public function clover(): Clover
    {
        if (!$this->has_clover()) {
            throw new Exception('Code Coverage report "Clover XML" has not been configured');
        }
        return $this->clover;
    }
    /**
     * @phpstan-assert-if-true !null $this->cobertura
     */
    public function has_cobertura(): bool
    {
        return $this->cobertura !== null;
    }
    /**
     * @throws Exception
     */
    public function cobertura(): Cobertura
    {
        if (!$this->has_cobertura()) {
            throw new Exception('Code Coverage report "Cobertura XML" has not been configured');
        }
        return $this->cobertura;
    }
    /**
     * @phpstan-assert-if-true !null $this->crap4j
     */
    public function has_crap4j(): bool
    {
        return $this->crap4j !== null;
    }
    /**
     * @throws Exception
     */
    public function crap4j(): Crap4j
    {
        if (!$this->has_crap4j()) {
            throw new Exception('Code Coverage report "Crap4J" has not been configured');
        }
        return $this->crap4j;
    }
    /**
     * @phpstan-assert-if-true !null $this->html
     */
    public function has_html(): bool
    {
        return $this->html !== null;
    }
    /**
     * @throws Exception
     */
    public function html(): Html
    {
        if (!$this->has_html()) {
            throw new Exception('Code Coverage report "HTML" has not been configured');
        }
        return $this->html;
    }
    /**
     * @phpstan-assert-if-true !null $this->openClover
     */
    public function has_open_clover(): bool
    {
        return $this->open_clover !== null;
    }
    /**
     * @throws Exception
     */
    public function open_clover(): Open_Clover
    {
        if (!$this->has_open_clover()) {
            throw new Exception('Code Coverage report "OpenClover XML" has not been configured');
        }
        return $this->open_clover;
    }
    /**
     * @phpstan-assert-if-true !null $this->php
     */
    public function has_php(): bool
    {
        return $this->php !== null;
    }
    /**
     * @throws Exception
     */
    public function php(): Php
    {
        if (!$this->has_php()) {
            throw new Exception('Code Coverage report "PHP" has not been configured');
        }
        return $this->php;
    }
    /**
     * @phpstan-assert-if-true !null $this->text
     */
    public function has_text(): bool
    {
        return $this->text !== null;
    }
    /**
     * @throws Exception
     */
    public function text(): Text
    {
        if (!$this->has_text()) {
            throw new Exception('Code Coverage report "Text" has not been configured');
        }
        return $this->text;
    }
    /**
     * @phpstan-assert-if-true !null $this->xml
     */
    public function has_xml(): bool
    {
        return $this->xml !== null;
    }
    /**
     * @throws Exception
     */
    public function xml(): Xml
    {
        if (!$this->has_xml()) {
            throw new Exception('Code Coverage report "XML" has not been configured');
        }
        return $this->xml;
    }
}