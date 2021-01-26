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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Log;

use Laminas\Log\Logger;
use Opus\Log\LevelFilter;
use PHPUnit\Framework\TestCase;

class LevelFilterTest extends TestCase
{
    public function testConstructor()
    {
        $filter = new LevelFilter(Logger::WARN);

        $warnEvent = ['priority' => Logger::WARN];
        $emergEvent = ['priority' => Logger::EMERG];
        $infoEvent = ['priority' => Logger::INFO];

        $this->assertTrue($filter->filter($warnEvent));
        $this->assertTrue($filter->filter($emergEvent));
        $this->assertFalse($filter->filter($infoEvent));
    }

    public function testSetLevel()
    {
        $filter = new LevelFilter(Logger::WARN);
        $filter->setLevel(Logger::INFO);

        $infoEvent = ['priority' => Logger::INFO];
        $debugEvent = ['priority' => Logger::DEBUG];

        $this->assertTrue($filter->filter($infoEvent));
        $this->assertFalse($filter->filter($debugEvent));
    }

    public function testSetLevelArgumentNull()
    {
        $filter = new LevelFilter(Logger::WARN);
        $filter->setLevel(null);

        $emergEvent = ['priority' => Logger::EMERG];
        $debugEvent = ['priority' => Logger::DEBUG];

        $this->assertTrue($filter->filter($emergEvent));
        $this->assertTrue($filter->filter($debugEvent));
    }

    public function testEnablingFilteringRestoresEqualOrLessOperator()
    {
        $filter = new LevelFilter(Logger::WARN);

        $filter->setLevel(null); // disable filtering
        $filter->setLevel(Logger::INFO); // enable filtering

        $this->assertEquals(Logger::INFO, $filter->getLevel());

        // Assert levels <= INFO are filtered
        $this->assertTrue($filter->filter(['priority' => Logger::WARN]));
        $this->assertTrue($filter->filter(['priority' => Logger::INFO]));

        // Assert levels > INFO are rejected
        $this->assertFalse($filter->filter(['priority' => Logger::DEBUG]));
    }

    public function testEnablingFilteringRestoresCustomOperator()
    {
        $filter = new LevelFilter(Logger::WARN, '=');

        $filter->setLevel(null);

        $this->assertTrue($filter->filter(['priority' => Logger::EMERG]));
        $this->assertTrue($filter->filter(['priority' => Logger::DEBUG]));

        $filter->setLevel(Logger::INFO);

        $this->assertFalse($filter->filter(['priority' => Logger::WARN]));
        $this->assertTrue($filter->filter(['priority' => Logger::INFO]));
        $this->assertFalse($filter->filter(['priority' => Logger::DEBUG]));
    }

    public function testSetLevelNegativeArgument()
    {
        $filter = new LevelFilter(Logger::WARN);

        $exceptionMessage = 'Level needs to be an integer and cannot be negative';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $filter->setLevel(-1);
    }

    public function testSetLevelStringArgument()
    {
        $filter = new LevelFilter(Logger::WARN);

        $exceptionMessage = 'Level needs to be an integer and cannot be negative';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $filter->setLevel('TestLevel');
    }

    public function testSetLevelStringNumericArgument()
    {
        $filter = new LevelFilter(Logger::WARN);

        $filter->setLevel('7');

        $this->assertTrue($filter->filter(['priority' => Logger::DEBUG]));
        $this->assertEquals(Logger::DEBUG, $filter->getLevel());
    }

    public function testGetLevel()
    {
        $filter = new LevelFilter(Logger::WARN);

        $this->assertEquals(Logger::WARN, $filter->getLevel());
    }

    public function testGetLevelOnNull()
    {
        $filter = new LevelFilter(Logger::WARN);
        $filter->setLevel(null);

        $this->assertNull($filter->getLevel());
    }
}
