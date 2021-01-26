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
 * @package     Opus
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus;

use Laminas\Log\Logger;
use Opus\Log\LevelFilter;

/**
 * Extension of Zend_Log for manipulating a logger with additional functionalities like changing the priority level.
 *
 * TODO Laminas Log allows multiple writers - How to refactor Log to handle this properly?
 */
class Log extends Logger
{

    /**
     * @var LevelFilter Filter object used to control the level of the log.
     */
    private $filter;

    /**
     * @var Logger Stores the default logger.
     */
    protected static $cachedReference;

    /**
     * Change the level of the filter.
     *
     * On null argument, it disables the filter. It doesn't effect or modify other filters of the loggers. So, if
     * applicable, they will still filter out log messages having higher level than their filter irrespective of what
     * setLevel() sets the level for the current one.
     *
     * @param int|null $level New level of the log
     */
    public function setLevel($level)
    {
        if ($level !== null && (! is_numeric($level) or $level < 0)) {
            throw new \InvalidArgumentException('Level needs to be an integer and cannot be negative');
        }

        if ($this->filter === null) {
            $this->filter = new LevelFilter($level);

            $writers = $this->getWriters();
            $writersArray = $writers->toArray();

            if (count($writersArray) > 0) {
                $writersArray[0]->addFilter($this->filter);
            }
        } else {
            $this->filter->setLevel($level);
        }
    }

    /**
     * Returns the level of the logger.
     *
     * @return int|null
     */
    public function getLevel()
    {
        if ($this->filter === null) {
            return null;
        } else {
            return $this->filter->getLevel();
        }
    }

    /**
     * Returns a default logger.
     *
     * @return Logger
     */
    public static function get()
    {
        if (! self::$cachedReference) {
            self::$cachedReference = \Zend_Registry::get('Zend_Log');
        }

        return self::$cachedReference;
    }

    /**
     * Drops any cached reference on logging facility to use.
     */
    public static function drop()
    {
        self::$cachedReference = null;
    }
}
