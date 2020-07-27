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
 * @category opus4-common
 * @Package Opus\Log
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
 */
class LogService
{
    /** @var Opus\Log\LogService Singleton instance of LogService. */
    private static $instance;

    /** @var \Zend_Log[] all log objects with lognames as keys. */
    private $loggers = [];

    /** @var string Format of log output. */
    private $defaultFormat;

    private $config;    //Zend_Config

    /** @var null path to the folder for log files. */
    private $logPath = null;

    /** @var string name of the log file. */
    private $logFileName;       //make it map of the log files associated with loggers

    /**
     * @var string name of the default log name.
     */
    private $defaultLogName = 'default';

    /** @var string default log level. */
    private $defaultLogLevel = 'INFO';

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     *
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

        $logFilePath = $this->getPath().$this->logFileName;
        $logFile = @fopen($logFilePath, 'a', false);
        if ($logFile === false) {
            $path = dirname($logFilePath);

            if (! is_dir($path)) {
                throw new Exception('Directory for logging does not exist');
            } else {
                throw new Exception('Failed to open logging file:' . $logFilePath);
            }
        }

        $logger = $this->createLogger($logFile);
        fclose($logFile);

        $this->applyLogLevel($logger);
        $this->addLog($logger);
        return $logger;
    }

    /**
     * To add log from external modules to loggers array.
     *
     * @param $logName String name of log
     * @param null $logger \Zend_Log
     *
     */
    public function addLog($logger)     //should include addLog($name, $logger)
    {
        $this->loggers[$this->logFileName] = $logger;
    }

    /**
     * Gets the configuration of logs
     *
     * @throws \Zend_Exception
     */
    public function getConfig()
    {
        $this->config = \Zend_Registry::get('Zend_Config');
        return $this->config;
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
            $this->setpath($config->workspacePath . '/log/');
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
        if (array_key_exists($this->defaultLogName.'.log', $this->loggers)) {
            return $logger = $this->loggers[$this->defaultLogName.'.log'];
        } else {
            return $this->createDefaultLog('default', 'opus.log');
        }
    }

    /**
     * To set the default log.
     *
     * @param $defaultLogName String
     * @param $defaultLogFileName String
     * @return \Zend_Log
     */
    public function createDefaultLog($defaultLogName, $defaultLogFileName)
    {
        $this->defaultLogName = $defaultLogName;
        return $this->createLog($defaultLogName, $defaultLogFileName);
    }

    /**
     * TODO - get the log configurations.
     *
     * @param $logName String
     * @return null
     */
    public function getLogConfig($logName)
    {
        return null;
    }

    /**
     * Apply the log level to the logger.
     *
     * @param $logger \Zend_Log object
     * @throws \Zend_Exception
     */
    public function applyLogLevel($logger)
    {
        $logLevel = $this->getLogLevel($logger);

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
     * @return string
     *
     *
     * TODO - $logger should not be defined yet.
     * May be make class variable flag and check where the function is called to write the warning and error messages.
     */
    public function getLogLevel($logger)
    {
        $config = $this->getConfig();

        if (isset($config->logging->log->level)) {
            $logLevelName = $config->logging->log->level;
        } else {
            $logLevelName = $this->defaultLogLevel;
            $logger->warn("Log level not configured, using '" . $this->defaultLogLevel . "'.");
        }

        $zendLogRefl = new \ReflectionClass('Zend_Log');

        $logLevel = $zendLogRefl->getConstant($logLevelName);

        if (empty($logLevel)) {
            $logLevel = Zend_Log::INFO;
            $logger->err("Invalid log level '" . $logLevelName .
                "' configured.");
        }

        return $logLevel;
    }

    /**
     * Sets the default log level.
     *
     * @param $logLevelName String
     *
     */
    public function setDefaultLogLevel($logLevelName)
    {
        $this->defaultLogLevel = $logLevelName;
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
    public function getLog($logName)
    {
        if ($logName == 'default') {
            return $this->getDefaultLog();
        }
        if (array_key_exists($logName.'.log', $this->loggers)) {
            return $logger = $this->loggers[$logName.'.log'];
        } else {
            try {
                return $this->createLog($logName);
            } catch (Exception $e) {
                throw new Exception('Unknown logger: '.$logName, 1);
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
        $format = $this->defaultFormat;
        if ($this->defaultFormat == null) {
            $config = $this->getConfig();
            if (isset($config->log->format)) {
                $format = $config->log->format;
            } else {
                $uniqueId = $this->getUniqueId();
                $format = '%timestamp% %priorityName% (%priority%, ID '.$uniqueId.'): %message%' . PHP_EOL;
            }
        }
        return $format;
    }

    /**
     * Write ID string to global variables, so we can identify/match individual runs.
     *
     */
    private function setUniqueId()
    {
        $GLOBALS['id_string'] = uniqid();
    }

    /**
     * Get the global variable ID string or set it if it doesn't exist
     *
     * @return mixed
     */
    public function getUniqueId()
    {
        if (! isset($GLOBALS['id_string'])) {
            $this->setUniqueId();
        }

        return $GLOBALS['id_string'];
    }

    /** TODO write generic functions for testing. */
}
