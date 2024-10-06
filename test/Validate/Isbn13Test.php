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
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Common\Validate;

use Opus\Common\Validate\Isbn13;
use OpusTest\Common\TestAsset\TestCase;

/**
 * Test cases for class Opus_Validate_Isbn13.
 *
 * @group       Isbn13Test
 */
class Isbn13Test extends TestCase
{
    /**
     * Data provider for valid arguments.
     *
     * @return array Array of invalid arguments.
     */
    public static function validDataProvider()
    {
        return [
            ['9785793181631'],
            ['978-3-86680-192-9'],
            ['978-5-7931-8163-1'],
            ['978-979-3182-63-6'],
            ['978 3 86680 192 9'],
            ['9785793181631'],
            ['978 5 7931 8163 1'],
            ['978 979 3182 63 6'],
        ];
    }

    /**
     * Data provider for invalid arguments.
     *
     * @return array Array of invalid arguments and a message.
     */
    public static function invalidDataProvider()
    {
        return [
            [null, 'Null value not rejected'],
            ['', 'Empty string not rejected'],
            [4711, 'Integer not rejected'],
            [true, 'Boolean not rejected'],
            ['4711-0815', 'Malformed string not rejected.'],
            ['980-3-86680-192-9', 'Wrong prefix not rejected.'],
            ['978-3-86680-192-5', 'Wrong check digit not rejected.'],
            ['978 5 7931 816321',  'Malformed string not rejected.'],
            ['97857931816321', 'Malformed string not rejected.'],
            ['978 3 86680-192-9', 'Mixed separators not rejected.'],
        ];
    }

    /**
     * Test validation of correct arguments.
     *
     * @param mixed $arg Value to check given by the data provider.
     * @dataProvider validDataProvider
     */
    public function testValidArguments($arg)
    {
        $validator = new Isbn13();
        $result    = $validator->isValid($arg);

        $codes = $validator->getErrors();
        $msgs  = $validator->getMessages();
        $err   = '';
        foreach ($codes as $code) {
            $err .= '(' . $msgs[$code] . ') ';
        }

        $this->assertTrue($result, $arg . ' should pass validation but validator says: ' . $err);
    }

    /**
     * Test validation of incorrect arguments.
     *
     * @param mixed  $arg Invalid value to check given by the data provider.
     * @param string $msg Error message.
     * @dataProvider invalidDataProvider
     */
    public function testInvalidArguments($arg, $msg)
    {
        $validator = new Isbn13();
        $this->assertFalse($validator->isValid($arg), $msg);
    }
}
