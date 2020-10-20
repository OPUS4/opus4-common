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

use Opus\Log\LogService;

class Log extends \Zend_Log
{
    public function __construct(\Zend_Log_Writer_Stream $writer = null)
    {
        parent::__construct($writer);
    }

    /**
     * Eliminates all prior priority filters and sets a new one.
     *
     * @param String $priority
     * @throws \Zend_Log_Exception
     */
    public function setPriority($priority)
    {
        if ($priority === null) {
            $priority = LogService::DEFAULT_PRIORITY;
        }

        $level = $this->convertPriorityToInt($priority);

        if ($level === null) {
            throw new \Exception("No such priority found as " . $priority);
        }

        $this->_filters = null;

        $priorityFilter = new \Zend_Log_Filter_Priority($level);
        $this->addFilter($priorityFilter);
    }

    /**
     * Returns the highest priority of the logger.
     *
     * @return String|null
     * @throws \ReflectionException
     */
    public function getPriority()
    {
        $priorities = [];

        $filters = $this->_filters;

        foreach ($filters as $filter) {
            $zendRefl = new \ReflectionClass($filter);
            $property = $zendRefl->getProperty('_priority');
            $property->setAccessible(true);
            $priorities[] = $property->getValue($filter);
        }

        $priority = $this->convertPriorityToString(min($priorities));

        return $priority;
    }

    /**
     * @param int $priority
     * @return String|null
     */
    public function convertPriorityToString($priority)
    {
        $zendLogRefl = new \ReflectionClass('Zend_Log');
        $constants = $zendLogRefl->getConstants();

        $levels = array_flip($constants);

        if (isset($levels[$priority])) {
            return $levels[$priority];
        } else {
            return null;
        }
    }

    /**
     * @param String $priorityName
     */
    public function convertPriorityToInt($priorityName)
    {
        $zendLogRefl = new \ReflectionClass('Zend_Log');
        $priority = $zendLogRefl->getConstant(strtoupper($priorityName));

        if ($priority !== false) {
            return $priority;
        } else {
            return null;
        }
    }
}
