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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Common\Log;

use InvalidArgumentException;
use Opus\Common\Log\LevelFilter;
use OpusTest\Common\TestAsset\TestCase;
use Zend_Log;

class LevelFilterTest extends TestCase
{
    public function testConstructor()
    {
        $filter = new LevelFilter(Zend_Log::WARN);

        $warnEvent  = ['priority' => Zend_Log::WARN];
        $emergEvent = ['priority' => Zend_Log::EMERG];
        $infoEvent  = ['priority' => Zend_Log::INFO];

        $this->assertTrue($filter->accept($warnEvent));
        $this->assertTrue($filter->accept($emergEvent));
        $this->assertFalse($filter->accept($infoEvent));
    }

    public function testSetLevel()
    {
        $filter = new LevelFilter(Zend_Log::WARN);
        $filter->setLevel(Zend_Log::INFO);

        $infoEvent  = ['priority' => Zend_Log::INFO];
        $debugEvent = ['priority' => Zend_Log::DEBUG];

        $this->assertTrue($filter->accept($infoEvent));
        $this->assertFalse($filter->accept($debugEvent));
    }

    public function testSetLevelArgumentNull()
    {
        $filter = new LevelFilter(Zend_Log::WARN);
        $filter->setLevel(null);

        $emergEvent = ['priority' => Zend_Log::EMERG];
        $debugEvent = ['priority' => Zend_Log::DEBUG];

        $this->assertTrue($filter->accept($emergEvent));
        $this->assertTrue($filter->accept($debugEvent));
    }

    public function testEnablingFilteringRestoresEqualOrLessOperator()
    {
        $filter = new LevelFilter(Zend_Log::WARN);

        $filter->setLevel(null); // disable filtering
        $filter->setLevel(Zend_Log::INFO); // enable filtering

        $this->assertEquals(Zend_Log::INFO, $filter->getLevel());

        // Assert levels <= INFO are accepted
        $this->assertTrue($filter->accept(['priority' => Zend_Log::WARN]));
        $this->assertTrue($filter->accept(['priority' => Zend_Log::INFO]));

        // Assert levels > INFO are rejected
        $this->assertFalse($filter->accept(['priority' => Zend_Log::DEBUG]));
    }

    public function testEnablingFilteringRestoresCustomOperator()
    {
        $filter = new LevelFilter(Zend_Log::WARN, '=');

        $filter->setLevel(null);

        $this->assertTrue($filter->accept(['priority' => Zend_Log::EMERG]));
        $this->assertTrue($filter->accept(['priority' => Zend_Log::DEBUG]));

        $filter->setLevel(Zend_Log::INFO);

        $this->assertFalse($filter->accept(['priority' => Zend_Log::WARN]));
        $this->assertTrue($filter->accept(['priority' => Zend_Log::INFO]));
        $this->assertFalse($filter->accept(['priority' => Zend_Log::DEBUG]));
    }

    public function testSetLevelNegativeArgument()
    {
        $filter = new LevelFilter(Zend_Log::WARN);

        $exceptionMessage = 'Level needs to be an integer and cannot be negative';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $filter->setLevel(-1);
    }

    public function testSetLevelStringArgument()
    {
        $filter = new LevelFilter(Zend_Log::WARN);

        $exceptionMessage = 'Level needs to be an integer and cannot be negative';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $filter->setLevel('TestLevel');
    }

    public function testSetLevelStringNumericArgument()
    {
        $filter = new LevelFilter(Zend_Log::WARN);

        $filter->setLevel('7');

        $this->assertTrue($filter->accept(['priority' => Zend_Log::DEBUG]));
        $this->assertEquals(Zend_Log::DEBUG, $filter->getLevel());
    }

    public function testGetLevel()
    {
        $filter = new LevelFilter(Zend_Log::WARN);

        $this->assertEquals(Zend_Log::WARN, $filter->getLevel());
    }

    public function testGetLevelOnNull()
    {
        $filter = new LevelFilter(Zend_Log::WARN);
        $filter->setLevel(null);

        $this->assertNull($filter->getLevel());
    }
}
