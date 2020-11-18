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
 * @category    Test
 * @package     OpusTest\Log
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Log;

use Opus\Log\LevelFilter;

class LevelFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testSetLevel()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);
        $filter->setLevel(\Zend_Log::INFO);

        $infoEvent = ['priority' => \Zend_Log::INFO];
        $debugEvent = ['priority' => \Zend_Log::DEBUG];

        $this->assertTrue($filter->accept($infoEvent));
        $this->assertFalse($filter->accept($debugEvent));
    }

    public function testSetLevelOnNullArgument()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);
        $filter->setLevel(null);

        $emergEvent = ['priority' => \Zend_Log::EMERG];
        $debugEvent = ['priority' => \Zend_Log::DEBUG];

        $this->assertTrue($filter->accept($emergEvent));
        $this->assertTrue($filter->accept($debugEvent));
    }

    public function testSetLevelChangedAfterNullArgument()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);
        $filter->setLevel(null);
        $filter->setLevel(\Zend_Log::INFO);

        $this->assertTrue($filter->accept(['priority' => \Zend_Log::INFO]));
        $this->assertSame(\Zend_Log::INFO, $filter->getLevel());
    }

    public function testSetLevelOnNegativeArgument()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);

        $exceptionMessage = 'Level should be of Integer type and cannot be negative';

        $this->setExpectedException(\InvalidArgumentException::class, $exceptionMessage);

        $filter->setLevel(-1);
    }

    public function testSetLevelOnStringArgument()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);

        $exceptionMessage = 'Level should be of Integer type and cannot be negative';

        $this->setExpectedException(\InvalidArgumentException::class, $exceptionMessage);

        $filter->setLevel('TestLevel');
    }

    public function testSetLevelStringNumericArgument()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);

        $filter->setLevel('7');

        $this->assertTrue($filter->accept(['priority' => \Zend_Log::DEBUG]));
        $this->assertEquals(\Zend_Log::DEBUG, $filter->getLevel());
    }

    public function testGetLevel()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);

        $this->assertEquals(\Zend_Log::WARN, $filter->getLevel());
    }

    public function testGetLevelOnNull()
    {
        $filter = new LevelFilter(\Zend_Log::WARN);
        $filter->setLevel(null);

        $this->assertNull($filter->getLevel());
    }

    public function testGetLevelOnInvertedConfig()
    {
        $filter = new LevelFilter(\Zend_Log::EMERG, '>=');

        $this->assertEquals(\Zend_Log::EMERG, $filter->getLevel());
    }
}
