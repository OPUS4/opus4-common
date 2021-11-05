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

use function in_array;

/**
 * Defines an abstract implementation of Opus_Validate_Mate.
 */
abstract class AbstractMate extends Zend_Validate_Abstract implements MateInterface
{
    /**
     * Hold the common validation result of the group of mates.
     *
     * @var bool
     */
    protected $common = false;

    /**
     * List of associated mate validators for broadcasting validation results.
     *
     * @var array
     */
    protected $mates = [];

    /**
     * Add another validator to the list of mates. Further, the validator adds
     * itself to the list of mates of the just added mate.
     *
     * @param MateInterface $mate Validator implementing Mate.
     */
    public function addMate(MateInterface $mate)
    {
        // If the mate is not already registerd and its not the instance itself,
        // then make it a member in the list of mates.
        if ((in_array($mate, $this->mates, true) === false) && ($this !== $mate)) {
            $this->mates[] = $mate;

            // Add this instance as mate to the new member.
            $mate->addMate($this);

            // Add all the instances mates to the list of mates of the new member.
            foreach ($this->mates as $mymate) {
                $mate->addMate($mymate);
            }
        }
    }

    /**
     * Inform all mates that the common validation result.
     */
    public function decideAllValid()
    {
        foreach ($this->mates as $mate) {
            $mate->decideValid();
        }
    }

    /**
     * Tell this specific validator to decide for validity.
     */
    public function decideValid()
    {
        $this->common = true;
    }

    /**
     * Validate the given value and inform all attended mates about a
     * maybe positive decision.
     *
     * @param string $value An value.
     * @return bool True the value is valid.
     */
    public function isValid($value)
    {
        // Immediatly return true if at least one of the mates has decided so.
        if ($this->common === true) {
            return true;
        }

        $this->_setValue($value);
        $result = $this->isValidCheck($value);

        if ($result === true) {
            $this->decideAllValid();
            $this->decideValid();
        }
        return $result;
    }

    /**
     * @param string $value
     * @return bool
     */
    abstract protected function isValidCheck($value);
}
