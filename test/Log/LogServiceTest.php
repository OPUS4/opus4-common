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

use Exception;
use FilesystemIterator;
use InvalidArgumentException;
use Opus\Common\Log\LogService;
use Opus\Common\OpusException;
use OpusTest\Common\TestAsset\TestCase;
use RecursiveDirectoryIterator;
use ReflectionClass;
use ReflectionException;
use Zend_Config;
use Zend_Log;

use function file_exists;
use function file_get_contents;
use function fopen;
use function is_dir;
use function mkdir;
use function preg_replace;
use function rmdir;
use function rtrim;
use function sys_get_temp_dir;
use function trim;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

class LogServiceTest extends TestCase
{
    public const DEFAULT_FORMAT = '%timestamp% %priorityName% (ID %runId%): %message%';

    /** @var LogService */
    private $logService;

    /** @var string */
    private $tempFolder;

    public function setUp()
    {
        parent::setUp();

        $tempFolder       = $this->createTempFolder();
        $this->tempFolder = $tempFolder;
        $this->createFolder('log');

        $this->logService = LogService::getInstance();
        $this->logService->setPath(null);
        $this->logService->setConfig(new Zend_Config([
            'workspacePath' => $tempFolder,
            'log'           => [
                'format' => self::DEFAULT_FORMAT,
                'level'  => 'WARN',
            ],
        ], true));
    }

    public function tearDown()
    {
        // reset singleton, because otherwise settings will carry over to next test
        $singleton  = LogService::getInstance();
        $reflection = new ReflectionClass($singleton);
        $instance   = $reflection->getProperty('instance');
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
        $logService = LogService::getInstance();
        $logService->setConfig(new Zend_Config([]));

        $this->expectException(OpusException::class);
        $this->expectExceptionMessage('Workspace path not found in configuration.');

        $logService->getPath();
    }

    /**
     * Test manually setting path for log files.
     */
    public function testSetPath()
    {
        $logService = LogService::getInstance();

        $path = rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $logService->setPath($path);
        $this->assertEquals($path, $logService->getPath());
    }

    public function testSetPathAddsDirectorySeparator()
    {
        $logService = LogService::getInstance();

        $path = rtrim(__DIR__, DIRECTORY_SEPARATOR);

        $logService->setPath($path);
        $this->assertEquals($path . DIRECTORY_SEPARATOR, $logService->getPath());
    }

    /**
     * Test getting configured default log priority.
     */
    public function testGetDefaultPriority()
    {
        $logService = $this->getLogService();

        $level = $logService->getDefaultPriority();

        $this->assertEquals(Zend_Log::WARN, $level);
    }

    /**
     * Test getting default log priority if not in configuration.
     */
    public function testGetDefaultPriorityNotConfigured()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new Zend_Config([
            'log' => ['level' => null],
        ]));

        $priority = $logService->getDefaultPriorityAsString();

        $this->assertEquals(LogService::DEFAULT_PRIORITY, $priority);
    }

    public function testSetDefaultPriority()
    {
        $logService = $this->getLogService();

        $logService->setDefaultPriority(Zend_Log::EMERG);

        $this->assertEquals(Zend_Log::EMERG, $logService->getDefaultPriority());
    }

    /**
     * Test setting custom default log priority.
     */
    public function testSetDefaultPriorityWithString()
    {
        $logService = $this->getLogService();

        $logService->setDefaultPriority('DEBUG');

        $this->assertEquals('DEBUG', $logService->getDefaultPriorityAsString());
    }

    /**
     * Configured format should be returned.
     */
    public function testGetDefaultFormat()
    {
        $logService = $this->getLogService();

        $format = $logService->getDefaultFormat();

        $expected = self::DEFAULT_FORMAT;

        $this->assertEquals($expected, $format);
    }

    /**
     * If not configured, hardcoded default should be returned.
     */
    public function testGetDefaultFormatNotConfigured()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new Zend_Config([
            'log' => ['format' => null],
        ]));

        $format = $logService->getDefaultFormat();

        $expected = LogService::DEFAULT_FORMAT;

        $this->assertEquals($expected, $format);
    }

    /**
     * Check custom default format can be set.
     */
    public function testSetDefaultFormat()
    {
        $logService = $this->getLogService();

        $logService->setDefaultFormat('%message%');

        $this->assertEquals('%message%', $logService->getDefaultFormat());
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
     * Test that format's %runId% placeholder is being replaced by runId.
     */
    public function testPrepareFormat()
    {
        $logService = $this->getLogService();

        $format = $logService->prepareFormat(self::DEFAULT_FORMAT);

        $runId = $logService->getRunId();

        $expected = preg_replace('/%runId%/', $runId, self::DEFAULT_FORMAT);

        $expected .= PHP_EOL;

        $this->assertContains("ID $runId", $format);
        $this->assertEquals($expected, $format);
    }

    /**
     * Test exception is thrown when format is null.
     */
    public function testPrepareFormatForNullFormat()
    {
        $logService = $this->getLogService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Format must not be null');

        $logService->prepareFormat(null);
    }

    /**
     * Test format has EOL.
     */
    public function testPrepareFormatHasEol()
    {
        $logService = $this->getLogService();

        $format = $logService->prepareFormat('%message%');

        $expected = '%message%' . PHP_EOL;

        $this->assertEquals($expected, $format);
    }

    /**
     * Test format has EOL only once even if EOL is in argument.
     */
    public function testPrepareFormatHasEolOnce()
    {
        $logService = $this->getLogService();

        $format = $logService->prepareFormat('%message%' . PHP_EOL);

        $expected = '%message%' . PHP_EOL;

        $this->assertEquals($expected, $format);
    }

    public function testGetDefaultLog()
    {
        $logService = $this->getLogService();

        $logger = $logService->getDefaultLog();

        $this->assertNotNull($logger);
        $this->assertInstanceOf(Zend_Log::class, $logger);
        $this->assertSame($logger, $logService->getDefaultLog());
    }

    /**
     * Test logger created by createLogger().
     *
     * @throws OpusException
     * @throws ReflectionException
     */
    public function testCreateLogger()
    {
        $logService = $this->getLogService();

        $logFilePath = $logService->getPath() . 'test.log';
        $logFile     = @fopen($logFilePath, 'a', false);

        $reflection = new ReflectionClass($logService);

        $createLogger = $reflection->getMethod('createLogger');
        $createLogger->setAccessible(true);
        $logger = $createLogger->invokeArgs($logService, ['%message% ID %runId%', Zend_Log::INFO, $logFile]);

        $this->assertNotNull($logger);

        $warnMessage  = 'WARN Message';
        $debugMessage = 'DEBUG Message';
        $logger->warn($warnMessage);
        $logger->debug($debugMessage);

        $runId = $logService->getRunId();

        $expected = $warnMessage . ' ID ' . $runId . PHP_EOL;

        $content = $this->readLogFile('test.log');

        $this->assertInstanceOf(Zend_Log::class, $logger);
        $this->assertContains($warnMessage, $content);
        $this->assertContains($runId, $content);
        $this->assertEquals($expected, $content);
        $this->assertNotContains($debugMessage, $content);
    }

    public function testGetLogGettingDefaultLogger()
    {
        $logService = $this->getLogService();

        $logger = $logService->getLog(LogService::DEFAULT_LOG);

        $this->assertNotNull($logger);
        $this->assertInstanceOf(Zend_Log::class, $logger);
        $this->assertSame($logger, $logService->getDefaultLog());
    }

    public function testGetLogConfiguredLog()
    {
        $logService = $this->getLogService();

        $logService->getConfig()->merge(new Zend_Config([
            'logging' => [
                'log' => [
                    'translation' => [
                        'format' => '%message%',
                        'file'   => 'translation.log',
                        'level'  => 'INFO',
                    ],
                ],
            ],
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
     */
    public function testGetLogForUnknownLog()
    {
        $logService = $this->getLogService();

        $logger = $logService->getLog('unknownLogger');

        $this->assertNotNull($logger);
        $this->assertInstanceOf(Zend_Log::class, $logger);

        $message = 'UNKNOWN LOGGER TEST';

        $logger->warn($message);

        $content = $this->readLogFile('unknownLogger.log');

        $this->assertContains($message, $content);
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
            'file'   => 'doi.log',
            'level'  => 'warn',
        ];

        $logService->getConfig()->merge(new Zend_Config([
            'logging' => [
                'log' => [
                    'doi' => $doiLogConfig,
                ],
            ],
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

        $logService->getConfig()->merge(new Zend_Config([
            'logging' => [
                'log' => [
                    'error' => [
                        'file' => 'error.log',
                    ],
                ],
            ],
        ]));

        $logConfig = $logService->getLogConfig('error');

        $this->assertEquals([
            'format' => $logService->getDefaultFormat(),
            'level'  => $logService->getDefaultPriorityAsString(),
            'file'   => 'error.log',
        ], $logConfig->toArray());
    }

    /**
     * Check if default configuration is returned if logging.log does not exist
     */
    public function testGetLogConfigForUnknownLog()
    {
        $logService = $this->getLogService();

        $doiLogConfig = [
            'format' => $logService->getDefaultFormat(),
            'file'   => 'doi.log',
            'level'  => $logService->getDefaultPriorityAsString(),
        ];

        $config = $logService->getLogConfig('doi');

        $this->assertEquals($doiLogConfig, $config->toArray());
    }

    public function testCreateLog()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog('translation');

        $this->assertInstanceOf(Zend_Log::class, $logger);

        $message = 'TRANSLATION LOG TEST MESSAGE';

        $logger->warn($message);

        $content = $this->readLogFile('translation.log');

        $this->assertContains($message, $content);
    }

    public function testCreateLogWithOptions()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog('error', 'ERR', 'ERROR %message%', 'opus-error.log');

        $this->assertInstanceOf(Zend_Log::class, $logger);

        $message = 'error test message';

        $logger->warn($message);
        $this->assertNotContains($message, $this->readLogFile('opus-error.log'));

        $logger->err($message);
        $this->assertContains("ERROR $message", $this->readLogFile('opus-error.log'));
    }

    public function testCreateLogWithInvalidPriorityName()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog('error', 'fehler', 'ERROR %message%');

        $this->assertInstanceOf(Zend_Log::class, $logger);

        $message = 'error test message';

        $logger->debug($message);
        $this->assertNotContains($message, $this->readLogFile('error.log'));

        // default log level in test setUp is WARN
        $logger->warn($message);
        $this->assertContains("ERROR $message", $this->readLogFile('error.log'));
    }

    public function testCreateLogWithPriorityValue()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog('error', Zend_Log::ERR, 'ERROR %message%');

        $this->assertInstanceOf(Zend_Log::class, $logger);

        $message = 'error test message';

        $logger->warn($message);
        $this->assertNotContains($message, $this->readLogFile('error.log'));

        $logger->err($message);
        $this->assertContains("ERROR $message", $this->readLogFile('error.log'));
    }

    public function testCreateDefaultLogWithCustomFilename()
    {
        $logService = $this->getLogService();

        $logger = $logService->createLog(LogService::DEFAULT_LOG, null, null, 'opus-console.log');

        $message = 'custom default log file test';

        $logger->warn($message);

        $content = $this->readLogFile('opus-console.log');

        $this->assertContains($message, $content);

        $this->assertSame($logger, $logService->getDefaultLog());
    }

    public function testAddLog()
    {
        $logService = $this->getLogService();

        $logger = new Zend_Log();

        $logService->addLog('mylog', $logger);

        $this->assertSame($logger, $logService->getLog('mylog'));
    }

    public function testAddLogWrongObjectType()
    {
        $logService = $this->getLogService();

        $object = new Zend_Config([]);

        $this->expectException(OpusException::class);
        $this->expectExceptionMessage('must be of type Zend_Log');

        $logService->addLog('myLog', $object);
    }

    public function testConvertPriorityFromString()
    {
        $logService = $this->getLogService();

        $this->assertEquals(Zend_Log::INFO, $logService->convertPriorityFromString('INFO'));
        $this->assertEquals(Zend_Log::INFO, $logService->convertPriorityFromString('info'));
        $this->assertEquals(Zend_Log::DEBUG, $logService->convertPriorityFromString('DEBUG'));
    }

    public function testConvertPriorityFromStringUnknownPriority()
    {
        $logService = $this->getLogService();

        $this->assertNull($logService->convertPriorityFromString('TestLevel'));
    }

    public function testConvertPriorityToString()
    {
        $logService = $this->getLogService();

        $this->assertEquals('INFO', $logService->convertPriorityToString(Zend_Log::INFO));
        $this->assertEquals('ERR', $logService->convertPriorityToString(Zend_Log::ERR));
        $this->assertEquals('EMERG', $logService->convertPriorityToString(Zend_Log::EMERG));
    }

    public function testConvertPriorityToStringUnknownPriority()
    {
        $logService = $this->getLogService();

        $this->assertNull($logService->convertPriorityToString(-1));
        $this->assertNull($logService->convertPriorityToString(99));
    }

    public function testLineBreaksInLogOutput()
    {
        $logService = $this->getLogService();

        $logService->setDefaultPriority('INFO');
        $logService->setDefaultFormat('%message%');
        $logger = $logService->getLog('translation');

        $logger->info('log message 1');
        $logger->info('log message 2');

        $content = $this->readLogFile('translation.log');

        $this->assertEquals(
            'log message 1' . PHP_EOL . 'log message 2' . PHP_EOL,
            $content
        );
    }

    public function testLineBreaksOnlyOne()
    {
        $logService = $this->getLogService();

        $logService->setDefaultPriority('INFO');
        $logService->setDefaultFormat('%message%' . PHP_EOL);
        $logger = $logService->getLog('translation');

        $logger->info('log message 1');
        $logger->info('log message 2');

        $content = $this->readLogFile('translation.log');

        $this->assertEquals(
            'log message 1' . PHP_EOL . 'log message 2' . PHP_EOL,
            $content
        );
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->logService;
    }

    /**
     * @return string
     */
    protected function createTempFolder()
    {
        $path  = sys_get_temp_dir();
        $path .= DIRECTORY_SEPARATOR . uniqid('opus4-common_test_');
        mkdir($path, 0777, true);
        return $path;
    }

    /**
     * TODO Move it from here for use in other tests as well.
     * TODO fix - has a generic name, but very specific function relying on a class variable (bad feeling here)
     *
     * @param string $folderName
     * @return string path to log folder.
     */
    protected function createFolder($folderName)
    {
        $path = $this->tempFolder . DIRECTORY_SEPARATOR . $folderName;
        mkdir($path, 0777, true);
        return $path;
    }

    /**
     * @param string $path
     */
    protected function removeFolder($path)
    {
        if ($path !== null && file_exists($path)) {
            if (is_dir($path)) {
                $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
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

    /**
     * @param string $name
     * @return false|string
     * @throws Exception
     */
    protected function readLogFile($name)
    {
        $path = $this->tempFolder . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $name;
        if (file_exists($path)) {
            return file_get_contents($path);
        } else {
            throw new Exception("log file '$name' not found");
        }
    }
}
