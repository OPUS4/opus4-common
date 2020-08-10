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
 * @Package     Opus\Log
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Log;

/**
 * Class to manage multiple loggers
 *
 * @package     Opus\Log
 *
 * TODO NOTE I think TODO tags are good, but they should be written like they are meant for other developers.
 *
 * TODO if addLog returns loggers even if they are unknown it behaves a lot like createLog;
 *      addLog should use createLog if a logger is unknown
 *
 * TODO we should configure the default options the same way like for other logger
 *      logging.log.default.format instead of 'log.format', but I would leave a decision until the end
 *
 * TODO Do we actually need the PHP_EOL at the end of the log format?
 */
class LogService
{

    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%, ID %runId%): %message%';

    const DEFAULT_PRIORITY = 'INFO';

    const DEFAULT_LOG = 'default';

    /** @var Opus\Log\LogService Singleton instance of LogService. */
    private static $instance;

    /** @var \Zend_Log[] All log objects with log names as keys. */
    private $loggers = [];

    /** @var string Format of log output. */
    private $defaultFormat;

    /** @var \Zend_Config Global configuration. */
    private $config;

    /** @var string Path to the folder for log files. */
    private $logPath = null;

    /** @var string name of the log file. */
    private $logFileName;       // TODO make it map of the log files associated with loggers

    /** @var string */
    private $runId;

    /** @var string Default log priority. */
    private $defaultPriority;

    /**
     * Private constructor, since LogService is supposed to be a singleton.
     */
    private function __construct()
    {
    }

    /**
     * Creates singleton instance of LogService.
     * @return Opus\Log\LogService
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new LogService();
        }
        return self::$instance;
    }

    /**
     * Creates a new logger and returns it.
     *
     * @param $logName string name of the log.
     * @param null $logFileName string optional name of the log file.
     *
     * @return \Zend_Log
     */
    public function createLog($logName, $logFileName = null)
    {
        if ($logFileName == null) {
            if ($logName == 'default') {
                $this->logFileName = 'opus.log';
            } else {
                $this->logFileName = $logName.'.log';
            }
        } else {
            $this->logFileName = $logFileName;
        }

        $logFilePath = $this->getPath() . $this->logFileName;
        $logFile = @fopen($logFilePath, 'a', false);
        if ($logFile === false) {
            $path = dirname($logFilePath);

            if (! is_dir($path)) {
                throw new \Exception('Directory for logging does not exist');
            } else {
                throw new \Exception('Failed to open logging file:' . $logFilePath);
            }
        }

        $logger = $this->createLogger($logFile);
        fclose($logFile);

        $this->applyLogLevel($logger);
        $this->addLog($logName, $logger);
        return $logger;
    }

    /**
     * To add log from external modules to loggers array.
     *
     * @param $logName String name of log
     * @param null $logger \Zend_Log
     *
     */
    public function addLog($name, $logger)
    {
        if (! $logger instanceof \Zend_Log) {
            // TODO throw exception
        }
        $this->loggers[$name] = $logger;
    }

    /**
     * Returns configuration.
     * @throws \Zend_Exception
     * @return null|\Zend_Config
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = \Zend_Registry::get('Zend_Config');
        }
        return $this->config;
    }

    /**
     * Sets configuration.
     * @param $config \Zend_Config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get path to the log folder.
     *
     * @return string path to the log folder.
     */
    public function getPath()
    {
        if ($this->logPath == null) {
            $config = $this->getConfig();
            // TODO check workspacePath
            $this->setPath($config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR);
        }

        return $this->logPath;
    }

    /**
     * Set the path to the log folder.
     *
     * @param $logPath String
     */
    public function setPath($logPath)
    {
        $this->logPath = $logPath;
    }

    /**
     * Returns the default log or creates new default log if one doesn't exist already.
     *
     * @return mixed|Zend_Log
     */
    public function getDefaultLog()
    {
        if (array_key_exists(self::DEFAULT_LOG.'.log', $this->loggers)) {
            return $this->loggers[self::DEFAULT_LOG.'.log'];
        } else {
            return $this->createLog(self::DEFAULT_LOG, 'opus.log');
        }
    }

    /**
     * Get a log's configurations.
     *
     * @param $logName String
     * @return \Zend_Config
     */
    public function getLogConfig($logName)
    {
        $config = $this->getConfig();

        $defaultConfig = new \Zend_Config([
            'format' => $this->getDefaultFormat(),
            'file' => $logName . '.log',
            'level' => $this->getDefaultLevel()
        ], true);

        if (isset($config->logging->log->$logName)) {
            $logConfig = $config->logging->log->$logName;
            return $defaultConfig->merge($logConfig);
        } else {
            return $defaultConfig;
        }
    }

    /**
     * Apply the log level to the logger.
     *
     * @param $logger \Zend_Log object
     * @throws \Zend_Exception
     */
    public function applyLogLevel($logger)
    {
        $logLevel = $this->getDefaultLevel();

        $priorityFilter = $this->createLogLevelFilter($logLevel);
        $logger->addFilter($priorityFilter);
        \Zend_Registry::set('LOG_LEVEL', $logLevel);
    }

    /**
     * Returns the log level.
     *
     * Gets either the log level name from configuration or the default log level name and
     *
     * @param $logger \Zend_Log object
     * @return int
     *
     *
     * TODO - $logger should not be defined yet.
     * May be make class variable flag and check where the function is called to write the warning and error messages.
     */
    public function getDefaultLevel()
    {
        $config = $this->getConfig();

        if (isset($config->logging->log->level)) {
            $logPriority = $config->logging->log->level;
        } else {
            $logPriority = $this->defaultPriority;
            // $logger->warn("Log level not configured, using '" . $this->defaultPriority . "'.");
        }

        $zendLogRefl = new \ReflectionClass('Zend_Log');

        $logLevel = $zendLogRefl->getConstant($logPriority);

        if (empty($logLevel)) {
            $logLevel = \Zend_Log::INFO;
            // $logger->err("Invalid log level '" . $logPriority .
            //     "' configured.");
        }

        return $logLevel;
    }

    /**
     * Sets the default log level.
     *
     * @param $level int
     *
     */
    public function setDefaultLevel($level)
    {
        $this->logLevel = $level;
    }

    /**
     * Sets the default log priority.
     *
     * @param $logPriority String
     *
     */
    public function setDefaultPriority($logPriority)
    {
        $this->defaultPriority = $logPriority;
    }

    /**
     * Return the default log priority.
     *
     * @return String
     */
    public function getDefaultPriority()
    {
        if ($this->defaultPriority == null) {
            $priority = self::DEFAULT_PRIORITY;

            $config = $this->getConfig();

            if (isset($config->log->level)) {
                $priority = $config->log->level;
                // TODO verify it is a valid priority string (in a separate function)
            }

            $this->defaultPriority = $priority;
        }

        return $this->defaultPriority;
    }

    /**
     * Creates the log level filter object and returns it.
     *
     * @param $logLevel
     * @return \Zend_Log_Filter_Priority
     * @throws \Zend_Log_Exception
     */
    protected function createLogLevelFilter($logLevel)
    {
        return new \Zend_Log_Filter_Priority($logLevel);
    }

    /**
     * Get a log or create one if not already exists.
     *
     * @param $logName String name of log
     * @return mixed|\Zend_Log
     */
    public function getLog($logName = null)
    {
        if ($logName = null || $logName == 'default') {
            return $this->getDefaultLog();
        }

        if (array_key_exists($logName.'.log', $this->loggers)) {
            return $logger = $this->loggers[$logName.'.log'];
        } else {
            try {
                return $this->createLog($logName);
            } catch (\Exception $e) {
                throw new \Exception('Unknown logger: '.$logName, 1);
            }
        }
    }

    /**
     * Create a new Zend_Log object.
     *
     * @param $logFile
     * @return \Zend_Log
     * @throws \Zend_Exception
     * @throws \Zend_Log_Exception
     */
    protected function createLogger($logFile)
    {
        $config = $this->getConfig();

        if (isset($config->logging->log->translation->file->format)) {
            $format = $config->logging->log->translation->file->format;
        } elseif ($this->defaultFormat == null) {
            $format = $this->getDefaultFormat();
        } else {
            $format = $this->defaultFormat;
        }

        $formatter = new \Zend_Log_Formatter_Simple($format);
        $writer = new \Zend_Log_Writer_Stream($logFile);
        $writer->setFormatter($formatter);

        return new \Zend_Log($writer);
    }

    /**
     * Set the default format.
     *
     * @param null|string $format Format of logging
     */
    public function setDefaultFormat($format)
    {
        $this->defaultFormat = $format;
    }

    /**
     * Return the format from configuration or the default format.
     *
     * @return string
     */
    public function getDefaultFormat()      //use placeholder for the ID string
    {
        if ($this->defaultFormat == null) {
            $config = $this->getConfig();

            $format = self::DEFAULT_FORMAT;

            if (isset($config->log->format)) {
                $format = $config->log->format;
            }

            $this->defaultFormat = $format;
        }

        $runId = $this->getRunId();

        return preg_replace('/%runId%/', $runId, $this->defaultFormat) . PHP_EOL;
    }

    /**
     * Write ID string to global variables, so we can identify/match individual runs.
     *
     */
    public function setRunId($runId)
    {
        $this->runId = $runId;
    }

    /**
     * Get the global variable ID string or set it if it doesn't exist.
     *
     * @return string
     */
    public function getRunId()
    {
        if ($this->runId == null) {
            $this->runId = uniqid();
        }

        return $this->runId;
    }

    /** TODO write generic functions for testing. NOTE not just for testing */
}
