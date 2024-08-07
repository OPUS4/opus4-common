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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Model\FieldType;

use Opus\Common\Log;
use Opus\Common\Model\AbstractFieldType;
use Zend_Exception;
use Zend_Form_Element;
use Zend_Validate_Exception;
use Zend_Validate_Regex;

use function array_key_exists;
use function error_get_last;
use function error_reporting;
use function is_array;
use function is_bool;
use function preg_match;

class RegexType extends AbstractFieldType
{
    /** @var string|null */
    private $regex;

    /** @var string */
    private $validation = 'none';

    /**
     * @return string|null
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param string|null $regex
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    /**
     * @return string|null
     */
    public function getValidation()
    {
        if ($this->regex === null) {
            return null; // wenn kein Regex gesetzt, dann braucht auch keine Validierung spezifiziert werden
        }

        return $this->validation;
    }

    /**
     * @param bool|string $validation
     */
    public function setValidation($validation)
    {
        if (is_bool($validation)) {
            $this->validation = $validation ? 'strict' : 'none';
        } else {
            $this->validation = $validation;
        }
    }

    /**
     * @param mixed|null $value
     * @return Zend_Form_Element
     * @throws Zend_Validate_Exception
     */
    public function getFormElement($value = null)
    {
        $element = parent::getFormElement();

        $validator = new Zend_Validate_Regex(['pattern' => '/' . $this->regex . '/']);
        $element->addValidator($validator);

        if ($value !== null) {
            $element->setValue($value);
        }

        return $element;
    }

    /**
     * @return string|null
     */
    public function getOptionsAsString()
    {
        return $this->regex;
    }

    /**
     * @param string|array|null $options
     * @throws Zend_Exception
     */
    public function setOptionsFromString($options)
    {
        if ($options === null) {
            return; // nothing to check
        }

        if (is_array($options)) {
            $this->setValidation(array_key_exists('validation', $options) && $options['validation']);
            $options = $options['options'];
        }

        // check if given option string is a valid regular expression

        // turn off error reporting and save current value for later restore
        $oldError = error_reporting(0);

        // add '/' delimiters to string that will be validated as a regex
        $stringToCheck = '/' . $options . '/';

        if (preg_match($stringToCheck, null) === false) {
            $error = error_get_last();
            $log   = Log::get();
            $log->warn('given type option regex ' . $options . ' is not valid: ' . $error);
        } else {
            $this->regex = $options;
        }

        // restore previous error reporting level
        error_reporting($oldError);
    }

    /**
     * @return bool
     */
    public function isStrictValidation()
    {
        return $this->validation === 'strict';
    }

    /**
     * @return string[]
     */
    public function getOptionProperties()
    {
        return ['regex', 'validation'];
    }
}
