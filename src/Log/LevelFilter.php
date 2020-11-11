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
 * @category    opus4-common
 * @package     Opus\Log
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Log;

/**
 * Class for manipulating the filter of a logger like setting the level and disabling it.
 */
class LevelFilter extends \Zend_Log_Filter_Priority
{
    private $operator;

    private $nullLevel;

    public function __construct($level, $operator = null)
    {
        parent::__construct($level, $operator);

        $this->operator = $this->_operator;
    }

    /**
     * Set the level of the filter.
     *
     * On null argument, filter is disabled.
     *
     * @param int $level
     */
    public function setLevel($level)
    {
        if ($level === null) {
            /**Filter is disabled by setting the level to lowest value and altering the comparison operator
            to greater or equal. */
            $this->_operator = '>=';
            $this->_priority = \Zend_Log::EMERG;
            $this->nullLevel = true;
        } elseif ($level < 0 || ! is_numeric($level)) {
            throw new \InvalidArgumentException('Level should be of Integer type and cannot be negative');
        } else {
            $this->_operator = $this->operator;
            $this->_priority = $level;
            $this->nullLevel = false;
        }
    }

    /**
     * Returns level of the filter.
     *
     * @return int|null
     */
    public function getLevel()
    {
        if ($this->nullLevel) {
            return null;
        }
        return $this->_priority;
    }
}
