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
 * @package     OpusTest
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest;

use Opus\Log;

class LogTest extends \PHPUnit_Framework_TestCase
{

    private $logFile;

    public function tearDown()
    {
        if (is_resource($this->logFile)) {
            fclose($this->logFile);
        }

        parent::tearDown();
    }

    public function testSetLevel()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setLevel(\Zend_Log::DEBUG);
        $debugMessage = 'Debug level message from testSetPriority';
        $opusLog->debug($debugMessage);
        $content = $this->readLog();

        $level = $opusLog->getLevel();

        $this->assertContains($debugMessage, $content);
        $this->assertSame(\Zend_Log::DEBUG, $level);
    }

    public function testSetLevelForFilterDisabling()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setLevel(null);

        $debugMessage = 'Debug level message from testSetPriorityWithNullPriority';
        $opusLog->debug($debugMessage);

        $content = $this->readLog();

        $this->assertContains($debugMessage, $content);
    }

    public function testSetLevelToNullHavingCustomLevels()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->addPriority('TEST', 8);
        $opusLog->setLevel(null);
        $opusLog->addPriority('TLEVEL', 9);

        $testMessage = 'Test level message';
        $opusLog->test($testMessage);

        $tlevelMessage = 'Tlevel message';
        $opusLog->tlevel($tlevelMessage);

        $content = $this->readLog();

        $this->assertContains($testMessage, $content);
        $this->assertContains($tlevelMessage, $content);
    }

    public function testSetLevelNotEffectingOtherFilters()
    {
        $opusLog = $this->getOpusLog();

        $filter = new \Zend_Log_Filter_Priority(\Zend_Log::WARN);
        $opusLog->addFilter($filter);

        $infoMessage = 'Info level Message';
        $opusLog->info($infoMessage);

        $opusLog->setLevel(\Zend_Log::NOTICE);
        $noticeMessage = 'Notice Level Message';
        $opusLog->notice($noticeMessage);

        $opusLog->setLevel(\Zend_Log::ERR);
        $errorMessage = 'Error level Message';
        $opusLog->err($errorMessage);

        $content = $this->readLog();

        $this->assertNotContains($infoMessage, $content);
        $this->assertNotContains($noticeMessage, $content);
        $this->assertContains($errorMessage, $content);
    }

    public function testSetLevelNegativePriority()
    {
        $opusLog = $this->getOpusLog();

        $this->setExpectedException(\InvalidArgumentException::class, 'Priority should be of Integer type and cannot be negative');

        $opusLog->setLevel(-1);
    }

    public function testSetLevelNotIntPriority()
    {
        $opusLog = $this->getOpusLog();

        $this->setExpectedException(\InvalidArgumentException::class, 'Priority should be of Integer type and cannot be negative');

        $opusLog->setLevel('TestPriority');
    }

    public function testGetLevel()
    {
        $opusLog = $this->getOpusLog();
        $priority = $opusLog->getLevel();

        $this->assertEquals(\Zend_Log::INFO, $priority);
    }

    public function testGetLevelAfterSetPriority()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setLevel(\Zend_Log::DEBUG);
        $priority = $opusLog->getLevel();

        $this->assertEquals(\Zend_Log::DEBUG, $priority);
    }

    public function testGetLevelOnNullPriority()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setLevel(null);
        $priority = $opusLog->getLevel();

        $this->assertNull($priority);
    }

    protected function getOpusLog()
    {
        $format = '%priorityName%: %message%' . PHP_EOL;
        $formatter = new \Zend_Log_Formatter_Simple($format);

        $this->logFile = fopen('php://temp', 'rw');
        $writer = new \Zend_Log_Writer_Stream($this->logFile);
        $writer->setFormatter($formatter);

        $logger = new Log($writer);

        $priority = \Zend_Log::INFO;
        $logger->setLevel($priority);

        return $logger;
    }

    protected function readLog()
    {
        rewind($this->logFile);
        $content = '';
        while (true) {
            $string = fgets($this->logFile);

            if (! $string) {
                break;
            }

            $content .= $string;
        }

        return $content;
    }

    protected function createTempFolder()
    {
        $path = sys_get_temp_dir();
        $path = $path . DIRECTORY_SEPARATOR . uniqid('opus4-common_test_');
        mkdir($path, 0777, true);
        return $path;
    }
}
