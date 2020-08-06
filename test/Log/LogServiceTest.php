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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Log;

use Opus\Log\LogService;

/**
 * Class LogServiceTest
 * @package OpusTest\Log
 *
 * TODO move generic test utility functions so they can be used in other test classes,
 *      current maybe into a helper class, later perhaps into a opus4-test library
 */
class LogServiceTest extends \PHPUnit_Framework_TestCase
{

    const DEFAULT_FORMAT = '%timestamp% %priorityName% (ID %runId%): %message%';

    private $logService;

    private $tempFolder;

    public function setUp()
    {
        parent::setUp();

        $tempFolder = $this->createTempFolder();
        $this->tempFolder = $tempFolder;

        $this->logService = LogService::getInstance();
        $this->logService->setConfig(new \Zend_Config([
            'workspacePath' => $tempFolder,
            'log' => [
                'format' => self::DEFAULT_FORMAT,
                'level' => 'WARN'
            ]
        ], true));
    }

    public function tearDown()
    {
        // reset singleton, because otherwise settings will carry over to next test
        $singleton = LogService::getInstance();
        $reflection = new \ReflectionClass($singleton);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);

        $this->removeFolder($this->tempFolder);

        parent::tearDown();
    }

    public function testGetInstance()
    {
        $logService = LogService::getInstance();
        $this->assertInstanceOf(LogService::class, $logService);
        $this->assertSame($logService, LogService::getInstance());
    }

    /**
     * Test getting default path for log files.
     */
    public function testGetPath()
    {
        $logService = LogService::getInstance();

        $path = $logService->getPath();

        $this->assertEquals(
            $this->tempFolder . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR,
            $path
        );
    }

    public function testGetPathNotConfigured()
    {
        // TODO what happens if workspacePath has not been configured
    }

    /**
     * Test manually setting path for log files.
     */
    public function testSetPath()
    {
        $logService = LogService::getInstance();
        $logService->setPath(__DIR__);
        $this->assertEquals(__DIR__, $logService->getPath());
    }

    /**
     * Test getting configured default log priority.
     */
    public function testGetDefaultPriority()
    {
        $logService = $this->getLogService();

        $level = $logService->getDefaultPriority();

        $this->assertEquals('WARN', $level);
    }

    /**
     * Test getting default log priority if not in configuration.
     */
    public function testGetDefaultPriorityNotConfigured()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new \Zend_Config([
            'log' => ['level' => null]
        ]));

        $priority = $logService->getDefaultPriority();

        $this->assertEquals(LogService::DEFAULT_PRIORITY, $priority);
    }

    /**
     * Test setting custom default log priority.
     */
    public function testSetDefaultPriority()
    {
        $logService = $this->getLogService();

        $logService->setDefaultPriority('DEBUG');

        $this->assertEquals('DEBUG', $logService->getDefaultPriority());
    }

    /**
     * Configured format should be returned.
     */
    public function testGetDefaultFormat()
    {
        $logService = $this->getLogService();

        $format = $logService->getDefaultFormat();

        $expected = preg_replace('/%runId%/', $logService->getRunId(), self::DEFAULT_FORMAT);
        $expected .= PHP_EOL;

        $this->assertEquals($expected, $format);
    }

    /**
     * If not configured, hardcoded default should be returned.
     */
    public function testGetDefaultFormatNotConfigured()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new \Zend_Config([
            'log' => ['format' => null]
        ]));

        $format = $logService->getDefaultFormat();

        $expected = preg_replace('/%runId%/', $logService->getRunId(), LogService::DEFAULT_FORMAT);
        $expected .= PHP_EOL;

        $this->assertEquals($expected, $format);
    }

    /**
     * Format should contain run ID.
     */
    public function testGetDefaultFormatContainsRunId()
    {
        $logService = $this->getLogService();

        $format = $logService->getDefaultFormat();

        $runId = $logService->getRunId();

        $this->assertContains("ID $runId", $format);
    }

    /**
     * Check custom default format can be set.
     */
    public function testSetDefaultFormat()
    {
        $logService = $this->getLogService();

        $logService->setDefaultFormat('%message%');

        $this->assertEquals('%message%' . PHP_EOL, $logService->getDefaultFormat());
    }

    /**
     * Check a custom format can use %runId% placeholder.
     */
    public function testGetDefaultFormatCustomFormatContainsRunId()
    {
        $logService = $this->getLogService();

        $logService->setDefaultFormat('ID %runId%: %message%');
        $runId = $logService->getRunId();

        $format = $logService->getDefaultFormat();

        $this->assertContains($runId, $format);
    }

    /**
     * Test that a unique ID is generated only once.
     */
    public function testGetRunId()
    {
        $logService = $this->getLogService();

        $id = $logService->getRunId();

        $this->assertNotNull($id);
        $this->assertInternalType('string', $id);
        $this->assertEquals($id, $logService->getRunId());
    }

    /**
     * Test that a custom unique ID can be set.
     */
    public function testSetRunId()
    {
        $logService = $this->getLogService();

        $logService->setRunId('customId');

        $this->assertEquals('customId', $logService->getRunId());
    }

    /**
     * TODO Check getting default log.
     */
    public function testGetDefaultLog()
    {
        $logService = $this->getLogService();

        $logger = $logService->getDefaultLog();

        $this->assertNotNull($logger);
        $this->assertInstanceOf(\Zend_Log::class, $logger);
        $this->assertSame($logger, $logService->getDefaultLog());
    }

    public function testGetLogGettingDefaultLogger()
    {
        $logService = $this->getLogService();

        $logger = $logService->getLog(LogService::DEFAULT_LOG);

        $this->assertNotNull($logger);
        $this->assertInstanceOf(\Zend_Log::class, $logger);
        $this->assertSame($logger, $logService->getDefaultLog());
    }

    /**
     * TODO Check if configured logger are returned properly configured.
     */
    public function testGetLogConfiguredLog()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new \Zend_Config([
            'logging' => ['log' => [
                'translation' => [
                    'format' => '%message%',
                    'file' => 'translation.log',
                    'level' => 'INFO'
                ]
            ]]
        ]));

        $logger = $logService->getLog('translation');

        $this->assertNotNull($logger);

        $debugMessage = 'debug level message';
        $logger->debug($debugMessage);

        $this->assertNotContains($debugMessage, $this->readLogFile('translation.log'));

        $infoMessage = 'info level message';
        $logger->info($infoMessage);

        $content = $this->readLogFile('translation.log');

        $this->assertContains($infoMessage, $content);
        $this->assertEquals($infoMessage, trim($content));
    }

    /**
     * Test exception is thrown if logger is unknown.
     *
     * TODO is that the best behavior or should we simply create a new logger with default settings
     *      using the unknown name for the new log file? (probably the better option)
     */
    public function testGetLogForUnknownLog()
    {
        $logService = $this->getLogService();

        $this->setExpectedException(UnknownLogException::class);

        $logService->getLog('unknownLogger');
    }

    /**
     * Calling getLog without a name should return default log.
     */
    public function testGetLogWithoutParameter()
    {
        $logService = $this->getLogService();

        $logger = $logService->getLog();

        $this->assertSame($logService->getDefaultLog(), $logger);
    }

    /**
     * Should return configuration options for a configured logger.
     */
    public function testGetLogConfig()
    {
        $logService = $this->getLogService();

        $doiLogConfig = [
            'format' => '%timestamp% %message%',
            'file' => 'doi.log',
            'level' => 'warn'
        ];

        $logService->getConfig()->merge(new \Zend_Config([
            'logging' => ['log' => [
                'doi' => $doiLogConfig
            ]]
        ]));

        $config = $logService->getLogConfig('doi');

        $this->assertEquals($doiLogConfig, $config->toArray());
    }

    /**
     * Check if defaults are set if log configuration is not complete.
     */
    public function testGetLogConfigAddsDefaultsForMissingOptions()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new \Zend_Config([
            'logging' => ['log' => [
                'error' => [
                    'file' => 'error.log'
                ]
            ]]
        ]));

        $logConfig = $logService->getLogConfig('error');

        $this->assertEquals([
            'format' => $logService->getDefaultFormat(),
            'level' => $logService->getDefaultLevel(),
            'file' => 'error.log'
        ], $logConfig->toArray());
    }

    /**
     * Check if default configuration is returned if logging.log does not exist
     */
    public function testGetLogConfigLoggingConfigurationMissing()
    {
        $logService = $this->getLogService();

        $doiLogConfig = [
            'format' => $logService->getDefaultFormat(),
            'file' => 'doi.log',
            'level' => $logService->getDefaultLevel()
        ];

        $config = $logService->getLogConfig('doi');

        $this->assertEquals($doiLogConfig, $config->toArray());
    }

    public function testGetLogConfigForUnknownLog()
    {
        // TODO if we allow unknown log (I think we should), this should return array with default options
    }

    /**
     * TODO Test creating a new logger.
     */
    public function testCreateLog()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog('translation');

        $this->assertInstanceOf(\Zend_Log::class, $logger);

        $message = 'TRANSLATION LOG TEST MESSAGE';

        $logger->info($message);

        $content = $this->readLogFile('translation.log');

        $this->assertContains($message, $content);
    }

    /**
     * TODO Test creating log with custom options.
     *
     * TODO because the number of options is limited this is okay, otherwise we could use options array
     *      with named options
     */
    public function testCreateLogWithOptions()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog('error', 'opus-error.log', 'ERR', 'ERROR %message%');

        $this->assertInstanceOf(\Zend_Log::class, $logger);

        $message = 'error test message';

        $logger->warn($message);
        $this->assertNotContains($message, $this->readLogFile('opus-error.log'));

        $logger->err($message);
        $this->assertContains("ERROR $message", $this->readLogFile('opus-error.log'));
    }

    /**
     * TODO Test customizing the log file for the default logger.
     */
    public function testCreateDefaultLogWithCustomFilename()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog(LogService::DEFAULT_LOG, 'opus-console.log');

        $message = 'custom default log file test';

        $logger->info($message);

        $content = $this->readLogFile('opus-console.log');

        $this->assertContains($message, $content);

        $this->assertSame($logger, $logService->getDefaultLog());
    }

    /**
     * TODO Test adding an externally created log object.
     */
    public function testAddLog()
    {
        $logService = $this->getLogService();

        $logger = new \Zend_Log();

        $logService->addLog('mylog', $logger);

        $this->assertSame($logger, $logService->getLog('mylog'));
    }

    /**
     * @return \Opus\Log\LogService
     */
    protected function getLogService()
    {
        return $this->logService;
    }

    protected function createTempFolder()
    {
        $path = sys_get_temp_dir();
        $path = $path . DIRECTORY_SEPARATOR . uniqid('opus4-common_test_');
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
                        $this->deleteFolder($file->getPathname());
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
        $path = $this->tempFolder . DIRECTORY_SEPARATOR . $name;
        if (file_exists($path)) {
            return file_get_contents($path);
        } else {
            throw new Exception("log file '$name' not found");
        }
    }
}
