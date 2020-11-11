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

use Opus\Log\LevelFilter;

/**
 * Class for manipulating a logger with additional functionalities like changing the priority level.
 */
class Log extends \Zend_Log
{
    private $filter;

    /**
     * Stores the default logger.
     *
     * @var \Zend_Log
     */
    protected static $cachedReference;

    public function __construct(\Zend_Log_Writer_Stream $writer = null)
    {
        parent::__construct($writer);
    }

    /**
     * Change the level of the filter. On null argument, it disables the filter.
     *
     * @param int $level
     * @throws \Zend_Log_Exception
     */
    public function setLevel($level)
    {
        if ($level !== null && ($level < 0 or ! is_numeric($level))) {
            throw new \InvalidArgumentException('Level should be of Integer type and cannot be negative');
        }
        if ($this->filter === null) {
            $this->filter = new LevelFilter($level);
            $this->addFilter($this->filter);
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
     * @return Log
     * @throws \Zend_Exception
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
