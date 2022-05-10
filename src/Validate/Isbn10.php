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
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Validate;

use function count;
use function end;
use function preg_match;
use function strlen;

/**
 * Defines an validator for ISBN-10 numbers.
 */
class Isbn10 extends Isbn
{
    /**
     * Error message key for invalid check digit.
     */
    const MSG_CHECK_DIGIT = 'checkdigit';

    /**
     * Error message key for malformed ISBN.
     */
    const MSG_FORM = 'form';

    /**
     * Error message templates.
     *
     * phpcs:disable
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::MSG_CHECK_DIGIT => "The check digit of '%value%' is not valid",
        self::MSG_FORM        => "'%value%' is malformed",
    ];

    /**
     * Validate the given ISBN-10 string.
     *
     * @param string $value An ISBN-10 number.
     * @return bool True if the given ISBN string is valid.
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        // check length
        if (strlen($value) !== 10 and strlen($value) !== 13) {
            $this->_error(self::MSG_FORM);
            return false;
        }

        // check form. ISBN10 can have 10 characters or 13 characters. If it has 10 characters, the first 9 are numbers
        // and the last can be additionally an X. If it has 13 characters, there are additionally 3 seperator of dashes
        // or spaces.
        if (preg_match('/^[\d]*((-|\s)?[\d]*){2}((-|\s)?[\dX])$/', $value) === 0) {
            $this->_error(self::MSG_FORM);
            return false;
        }

        // check for mixed separators
        if ((preg_match('/-/', $value) > 0) and (preg_match('/\s/', $value) > 0)) {
            $this->_error(self::MSG_FORM);
            return false;
        }

        $digits = $this->extractDigits($value);
        if (count($digits) != 10) {
            $this->_error(self::MSG_FORM);
            return false;
        }

        // Calculate and compare check digit
        $checkdigit = $this->calculateCheckDigit($digits);
        if ($checkdigit !== end($digits)) {
            $this->_error(self::MSG_CHECK_DIGIT);
            return false;
        }

        return true;
    }

    /**
     * Calculate the checkdigit from a given array of 10 digits.
     *
     * @param array $digits Array of digits that form ISBN.
     * @return string The check digit.
     */
    protected function calculateCheckDigit(array $digits)
    {
        $z     = $digits;
        $z[10] = 0;
        for ($i = 1; $i < 10; $i++) {
            $z[10] += $i * $z[$i - 1];
        }
        $z[10] %= 11;
        if ($z[10] == 10) {
            $z[10] = 'X';
        }
        return "$z[10]";
    }
}
