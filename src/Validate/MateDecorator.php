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

use Zend_Validate_Interface;

/**
 * Defines an extension for validators so that the interface and functionality
 * of Opus_Validate_AbstractMate is provided.
 *
 * @see AbstractMate
 */
class MateDecorator extends AbstractMate
{
    /**
     * Validator object that is decorated.
     *
     * @var Zend_Validate_Interface
     */
    protected $decorated;

    /**
     * Create decoration for given validator.
     *
     * @param Zend_Validate_Interface $validator Validator to be decorated.
     */
    public function __construct(Zend_Validate_Interface $validator)
    {
        $this->decorated = $validator;
    }

    /**
     * Create and return a decorated validator.
     *
     * @param Zend_Validate_Interface $validator Validator to be decorated.
     * @return self Decorator instance.
     */
    public static function decorate(Zend_Validate_Interface $validator)
    {
        return new MateDecorator($validator);
    }

    /**
     * Call the decorated validator. This method is called by Opus_Validate_AbstractMate::isValid().
     *
     * @param mixed $value Value to validate.
     * @return bool Whatever the decorated validators isValid() method returns.
     */
    protected function isValidCheck($value)
    {
        return $this->decorated->isValid($value);
    }
}
