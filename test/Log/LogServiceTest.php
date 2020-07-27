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
 * @package     Opus
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Log;

use Opus\Log\LogService;

class LogServiceTest extends \PHPUnit_Framework_TestCase
{
    private $logInstance;
    private $logger;

    public function setUp()
    {
        $this->logInstance = LogService::getInstance();

        $this->logger = $this->LogInstance->getDefaultLog();    //WRONG how to get comparable log
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(LogService::class, $this->logInstance);
    }

    public function testGetLog()
    {
        $this->markTestIncomplete();

        $log = $this->logInstance->getLog('default');      //DONE should pass only log name, not filename
        $this->assertSame($log, $this->logger);
    }

    public function testCreateDefaultLog()
    {
        $this->markTestIncomplete();

        $log = $this->logInstance->setDefaultLog();
        $this->assertSame($log, $this->logger);
    }

    public function testGetDefaultLog()
    {
        $log = $this->logInstance->getDefaultLog();
        $this->markTestIncomplete();
        $this->assertSame($log, $this->logger);
    }

    public function testAddLog()
    {
        $this->markTestIncomplete();
    }

    public function testCreateDefaultCustomLog()
    {
        $this->markTestIncomplete();
    }
}