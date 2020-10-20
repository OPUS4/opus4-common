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

namespace OpusTest\Log;

use Opus\Log\LogService;

class OpusLogTest extends \PHPUnit_Framework_TestCase
{
    private $opusLog;

    private $tempFolder;

    public function setUp()
    {
        parent::setUp();

        $tempFolder = $this->createTempFolder();
        $this->tempFolder = $tempFolder;
        $this->createFolder('log');

        $logService = LogService::getInstance();
        $logService->setConfig(new \Zend_Config([
            'workspacePath' => $tempFolder
        ], true));

        $this->opusLog = $logService->createLog(LogService::DEFAULT_LOG);
    }

    public function tearDown()
    {
        $singleton = LogService::getInstance();
        $reflection = new \ReflectionClass($singleton);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);

        $this->removeFolder($this->tempFolder);

        parent::tearDown();
    }

    public function testSetPriority()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setPriority('debug');

        $debugMessage = 'Debug level message from testSetPriority';
        $opusLog->debug($debugMessage);
        $content = $this->readLogFile('default.log');

        $this->assertContains($debugMessage, $content);
    }

    public function testSetPriorityWithNullPriority()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setPriority(null);

        $debugMessage = 'Debug level message from testSetPriorityWithNullPriority';
        $opusLog->debug($debugMessage);

        $infoMessage = 'Info level message from testSetPriorityWithNullPriority';
        $opusLog->info($infoMessage);

        $content = $this->readLogFile('default.log');

        $this->assertContains($infoMessage, $content);
    }

    public function testSetPriorityUnknownPriority()
    {
        $opusLog = $this->getOpusLog();

        $this->setExpectedException(\Exception::class, "No such priority found as TestPriority");

        $opusLog->setPriority('TestPriority');
    }

    public function testGetPriority()
    {
        $opusLog = $this->getOpusLog();
        $priority = $opusLog->getPriority();

        $this->assertEquals('INFO', $priority);
    }

    public function testGetPriorityAfterSetPriority()
    {
        $opusLog = $this->getOpusLog();
        $opusLog->setPriority('debug');
        $priority = $opusLog->getPriority();

        $this->assertEquals('DEBUG', $priority);
    }

    public function testConvertPriorityToInt()
    {
        $opusLog = $this->getOpusLog();

        $this->assertEquals(\Zend_Log::DEBUG, $opusLog->convertPriorityToInt('debug'));
        $this->assertEquals(\Zend_Log::CRIT, $opusLog->convertPriorityToInt('CRIT'));
        $this->assertEquals(\Zend_Log::INFO, $opusLog->convertPriorityToInt('Info'));
    }

    public function testConvertPriorityToIntUnknownPriority()
    {
        $opusLog = $this->getOpusLog();

        $this->assertNull($opusLog->convertPriorityToInt('TestLevel'));
    }

    public function testConvertPriorityToString()
    {
        $opusLog = $this->getOpusLog();

        $this->assertEquals('INFO', $opusLog->convertPriorityToString(\Zend_Log::INFO));
        $this->assertEquals('ERR', $opusLog->convertPriorityToString(\Zend_Log::ERR));
        $this->assertEquals('EMERG', $opusLog->convertPriorityToString(\Zend_Log::EMERG));
    }

    public function testConvertPriorityToStringUnknownPriority()
    {
        $opusLog = $this->getOpusLog();

        $this->assertNull($opusLog->convertPriorityToString(10));
    }

    public function getOpusLog()
    {
        return $this->opusLog;
    }

    protected function createTempFolder()
    {
        $path = sys_get_temp_dir();
        $path = $path . DIRECTORY_SEPARATOR . uniqid('opus4-common_test_');
        mkdir($path, 0777, true);
        return $path;
    }

    /**
     * Use helper methods from OPUSVIER-4400
     *
     * @return String path to log folder.
     */
    protected function createFolder($folderName)
    {
        $path = $this->tempFolder . DIRECTORY_SEPARATOR . $folderName;
        mkdir($path, 0777, true);
        return $path;
    }

    protected function removeFolder($path)
    {
        if (! is_null($path) && file_exists($path)) {
            if (is_dir($path)) {
                $iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
                foreach ($iterator as $file) {
                    if ($file->isDir()) {
                        $this->removeFolder($file->getPathname());
                    } else {
                        unlink($file->getPathname());
                    }
                }
                rmdir($path);
            }
        }
    }

    protected function readLogFile($name)
    {
        $path = $this->tempFolder . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $name;
        if (file_exists($path)) {
            return file_get_contents($path);
        } else {
            throw new \Exception("log file '$name' not found");
        }
    }
}
