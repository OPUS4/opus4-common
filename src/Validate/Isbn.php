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

namespace Opus\Validate;

use Zend_Validate_Abstract;

use function count;
use function preg_split;
use function str_split;

/**
 * Validator for ISBN values.
 */
class Isbn extends Zend_Validate_Abstract
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
        self::MSG_CHECK_DIGIT => "The check digit of '%value%' is not valid.",
        self::MSG_FORM        => "'%value%' is malformed.",
    ];
    // phpcs:enable

    /**
     * Validate the given ISBN string using ISBN-10 or ISBN-13 validators respectivly.
     *
     * @param string $value An ISBN number.
     * @return bool True if the given ISBN string is valid.
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        $isbnValidator = null;
        switch (count($this->extractDigits($value))) {
            case 10:
                $isbnValidator = new Isbn10();
                break;
            case 13:
                $isbnValidator = new Isbn13();
                break;
            default:
                $this->_error(self::MSG_FORM);
                $result = false;
                break;
        }

        if ($isbnValidator !== null) {
            $result = $isbnValidator->isValid($value);
            foreach ($isbnValidator->getErrors() as $error) {
                $this->_error($error);
            }
        }

        return $result;
    }

    /**
     * @param string $value
     * @return array with seperated character except the seperators
     */
    public function extractDigits($value)
    {
        $isbnParts = preg_split('/(-|\s)/', $value);

        // Separate digits for checkdigit calculation
        $digits = [];
        for ($i = 0; $i < count($isbnParts); $i++) {
            foreach (str_split($isbnParts[$i]) as $digit) {
                $digits[] = $digit;
            }
        }

        return $digits;
    }
}
