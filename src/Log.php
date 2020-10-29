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

namespace Opus;

use Opus\Log\LevelFilter;

class Log extends \Zend_Log
{
    private $filter;

    public function __construct(\Zend_Log_Writer_Stream $writer = null)
    {
        parent::__construct($writer);
    }

    /**
     * Change the priority of the filter.
     *
     * TODO On null, the function disables the filter
     *
     * @param int $priority
     * @throws \Zend_Log_Exception
     */
    public function setPriority($priority)
    {
        if ($priority !== null && ($priority < 0 || gettype($priority) !== 'integer')) {
            throw new \Exception('Priority should be of Integer type and cannot be negative');
        }
        if ($this->filter === null) {
            $this->filter = new LevelFilter($priority);
            $this->addFilter($this->filter);
        } else {
            if ($priority === null) {
                $highestPriority = max(array_flip($this->_priorities));
                $this->filter->setPriority($highestPriority);
            } else {
                $this->filter->setPriority($priority);
            }
        }
    }

    /**
     * Returns the highest priority of the logger.
     *
     * @return int|null
     */
    public function getPriority()
    {
        if ($this->filter === null) {
            return null;
        } else {
            return $this->filter->getPriority();
        }
    }
}
