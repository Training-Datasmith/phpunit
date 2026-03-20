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
namespace Php_Unit\Text_Ui\Xml_Configuration\Logging;

use Php_Unit\Text_Ui\Xml_Configuration\Exception;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Test_Dox\Html as TestDoxHtml;
use Php_Unit\Text_Ui\Xml_Configuration\Logging\Test_Dox\Text as TestDoxText;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Logging
{
    public function __construct(private ?Junit $junit, private ?Otr $otr, private ?Team_City $team_city, private ?Test_Dox_Html $test_dox_html, private ?Test_Dox_Text $test_dox_text)
    {
    }
    public function has_junit(): bool
    {
        return $this->junit !== null;
    }
    /**
     * @throws Exception
     */
    public function junit(): Junit
    {
        if ($this->junit === null) {
            throw new Exception('Logger "JUnit XML" is not configured');
        }
        return $this->junit;
    }
    public function has_otr(): bool
    {
        return $this->otr !== null;
    }
    /**
     * @throws Exception
     */
    public function otr(): Otr
    {
        if ($this->otr === null) {
            throw new Exception('Logger "Open Test Reporting XML" is not configured');
        }
        return $this->otr;
    }
    public function has_team_city(): bool
    {
        return $this->team_city !== null;
    }
    /**
     * @throws Exception
     */
    public function team_city(): Team_City
    {
        if ($this->team_city === null) {
            throw new Exception('Logger "Team City" is not configured');
        }
        return $this->team_city;
    }
    public function has_test_dox_html(): bool
    {
        return $this->test_dox_html !== null;
    }
    /**
     * @throws Exception
     */
    public function test_dox_html(): Test_Dox_Html
    {
        if ($this->test_dox_html === null) {
            throw new Exception('Logger "TestDox HTML" is not configured');
        }
        return $this->test_dox_html;
    }
    public function has_test_dox_text(): bool
    {
        return $this->test_dox_text !== null;
    }
    /**
     * @throws Exception
     */
    public function test_dox_text(): Test_Dox_Text
    {
        if ($this->test_dox_text === null) {
            throw new Exception('Logger "TestDox Text" is not configured');
        }
        return $this->test_dox_text;
    }
}