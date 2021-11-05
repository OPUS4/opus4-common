<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Tests
 * @package     Opus_Validate
 * @author      Maximilian Salomon (salomom@zib.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Validate;

use Opus\Validate\Issn;
use OpusTest\TestAsset\TestCase;

/**
 * Unit tests for Opus\Validate\Issn.
 * @coversDefaultClass \Opus\Validate\Issn
 */
class IssnTest extends TestCase
{

    /**
     * @return array with valid issn's
     */
    public function validIssnProvider()
    {
        return [
            ['1050-124X'],
            ['0317-8471'],
            ['1062-5127'],
            ['0025-5858'],
            ['0001-3218'],
        ];
    }

    /**
     * @return array with invalid issn's
     */
    public function invalidIssnProvider()
    {
        return [
            [null],
            [true],
            ['12345478'],
            ['12456-65478'],
            ['123456789'],
            ['1050 124X'],
            ['1050_124X'],
            ['1050-1242'],
        ];
    }

    /**
     * @return array with invalid issn's and it's error-messages and error-keys
     */
    public function messageIssnProvider()
    {
        return [
            ['12345478', 'form', "'12345478' is malformed."],
            ['12456-65478', 'form', "'12456-65478' is malformed."],
            ['123456789', 'form', "'123456789' is malformed."],
            ['1050 124X', 'form', "'1050 124X' is malformed."],
            ['1050_124X', 'form', "'1050_124X' is malformed."],
            ['1050-1242', 'checkdigit', "The check digit of '1050-1242' is not valid."],
            ['1062-512X', 'checkdigit', "The check digit of '1062-512X' is not valid."],
            ['0025-5856', 'checkdigit', "The check digit of '0025-5856' is not valid."],
            ['0001-3211', 'checkdigit', "The check digit of '0001-3211' is not valid."],
        ];
    }

    /**
     * Unittest for isValid with valid Arguments.
     * @covers ::isValid
     * @covers ::calculateCheckDigit
     * @dataProvider validIssnProvider
     */
    public function testValidArguments($arg)
    {
        $validator = new Issn();
        $this->assertTrue($validator->isValid($arg));
    }

    /**
     * Unittest for isValid with invalid Arguments.
     * @covers ::isValid
     * @covers ::calculateCheckDigit
     * @dataProvider invalidIssnProvider
     */
    public function testInvalidArguments($arg)
    {
        $validator = new Issn();
        $this->assertFalse($validator->isValid($arg));
    }

    /**
     * Unittest to check the error-messages for an invalid ISSN.
     * @covers ::isValid
     * @covers ::calculateCheckDigit
     * @dataProvider MessageIssnProvider
     */
    public function testErrorMessageForm($arg, $err, $msg)
    {
        $validator = new Issn();
        $this->assertFalse($validator->isValid($arg));
        $this->assertContains($err, $validator->getErrors());
        $this->assertContains($msg, $validator->getMessages());
    }
}
