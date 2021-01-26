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
 * @copyright   Copyright (c) 2020-2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest;

use Laminas\Log\Filter\Priority;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use Opus\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
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
        $opusLog->setLevel(Logger::DEBUG);

        $debugMessage = 'Debug level message from testSetLevel';
        $opusLog->debug($debugMessage);

        $this->assertContains($debugMessage, $this->readLog());
        $this->assertEquals(Logger::DEBUG, $opusLog->getLevel());
    }

    public function testSetLevelForFilterDisabling()
    {
        $opusLog = $this->getOpusLog();

        $debugMessage = 'Debug level message from testSetLevelWithNullLevel';
        $infoMessage = 'Info level message';

        $opusLog->info($infoMessage);
        $opusLog->debug($debugMessage);

        $content = $this->readLog();
        $this->assertContains($infoMessage, $content);
        $this->assertNotContains($debugMessage, $content);

        $opusLog->setLevel(null);
        $opusLog->debug($debugMessage);

        $content = $this->readLog();
        $this->assertContains($debugMessage, $content);
    }

    public function testSetLevelNotAffectingOtherFilters()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setLevel(Logger::INFO);

        $additionalFilter = new Priority(Logger::WARN);

        $writers = $opusLog->getWriters()->toArray();

        $writers[0]->addFilter($additionalFilter);

        // INFO message gets rejected by additional filter
        $infoMessage = 'Info level Message';
        $opusLog->info($infoMessage);
        $this->assertNotContains($infoMessage, $this->readLog());

        // After setting level to DEBUG the additional filter still rejects DEBUG message
        $opusLog->setLevel(Logger::DEBUG);
        $debugMessage = 'Debug Level Message';
        $opusLog->debug($debugMessage);
        $this->assertNotContains($debugMessage, $this->readLog());

        // An ERROR level message is accepted by both filters
        $errorMessage = 'Error level Message';
        $opusLog->err($errorMessage);
        $this->assertContains($errorMessage, $this->readLog());
    }

    public function testSetLevelNegativeLevel()
    {
        $opusLog = $this->getOpusLog();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Level needs to be an integer and cannot be negative');

        $opusLog->setLevel(-1);
    }

    public function testSetLevelStringArgument()
    {
        $opusLog = $this->getOpusLog();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Level needs to be an integer and cannot be negative');

        $opusLog->setLevel('TestLevel');
    }

    public function testSetLevelStringLevel()
    {
        $opusLog = $this->getOpusLog();

        $opusLog->setLevel('7');

        $this->assertEquals(Logger::DEBUG, $opusLog->getLevel());
    }

    public function testGetLevel()
    {
        $opusLog = $this->getOpusLog();

        $this->assertEquals(Logger::INFO, $opusLog->getLevel());
    }

    public function testGetLevelReturnsNull()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setLevel(null);

        $this->assertNull($opusLog->getLevel());
    }

    public function testGet()
    {
        \Zend_Registry::set('Zend_Log', $this->getOpusLog());
        $log = Log::get();

        $this->assertSame(\Zend_Registry::get('Zend_Log'), $log);
    }

    /**
     * Check if the logger instance gets dropped and new logger is created.
     * @throws \Zend_Exception
     */
    public function testDrop()
    {
        $log = $this->getOpusLog();
        \Zend_Registry::set('Zend_Log', $log);

        $logger1 = Log::get();
        Log::drop();
        $logger2 = Log::get();

        $this->assertNotSame($logger1, $logger2);
    }

    protected function getOpusLog()
    {
        $format = '%priorityName%: %message%' . PHP_EOL;
        $formatter = new Simple($format);

        $this->logFile = fopen('php://memory', 'rw');
        $writer = new Stream($this->logFile);
        $writer->setFormatter($formatter);

        $logger = new Log();
        $logger->addWriter($writer);

        $level = Logger::INFO;
        $logger->setLevel($level);

        return $logger;
    }

    protected function readLog()
    {
        rewind($this->logFile);
        $content = '';
        while ($string = fgets($this->logFile)) {
            $content .= $string;
        }

        return $content;
    }
}
