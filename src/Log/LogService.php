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

    /** @var Zend_Log[] all log objects with lognames as keys.*/		//DONE Look how to put comment for array ZendLog
    private $loggers = [];

    private $logFile;       //file pointer resource to the logfile.

    /** @var string Format of log output. */
    private $defaultFormat;		//DONE rename to defaulFormat

    private $config;    //Zend_Config

    /** @var null path to the folder for log files. */
    private $logPath = null;

    /** @var string name of the log file. */		
    private $logFileName;		//make it map of the log files associated with loggers

    /**
     * @var string name of the default log name.
     */
    private $defaultLogName = 'default';

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
     * @param $logName string name of the log.
     * @param null $logFileName string optional name of the log file.
     */
    public function createLog($logName, $logFileName = null)
    {	//DONE put the code of getConfig instead of calling

        $config = \Zend_Registry::get('Zend_Config')		//DONE shouldn't be here, try getpath()

        $logFilePath = getPath();

        if ($logFileName == null) {
            if ($logName == 'default') {
                $this->logFileName = 'opus.log';
            } else {
                $this->logFileName = $logName.'.log';
            }
        } else {
            $this->logFileName = $logFileName;
        }

        $this->logFile = @fopen($logfilePath.$this->logFileName, 'a', false);
        if ($this->logFile === false) {
            $path = dirname($logfilePath);

            if (! is_dir($path)) {
                throw new Exception('Directory for logging does not exist');
            } else {
                throw new Exception('Failed to open logging file:' . $logfilePath);
            }
        }
    }

    /**
     * @throws \Zend_Exception
     */
    public function getConfig()
    {
        $this->config = \Zend_Registry::get('Zend_Config');
    }

    public function setConfig($config)
    {
    	# code...
    }

    /**
     * get path to the log file.
     *
     * @return string path
     */
    public function getPath()
    {
        $logfilePath = $this->logPath == null ? $this->setPath($config->workspacePath . '/log/') : $this->logPath;		
    }

    /**
     * set path to the log file
     *
     * @param $logPath String optional to set the path.
     *
     * @return null path
     */
    public function setPath($logPath)
    {
        $this->logPath  = $logPath;
    }

    /**
     * Returns the default log
     *
     * @return mixed|Zend_Log
     */
    public function getDefaultLog()
    {
        if (array_key_exists($this->defaultLogName.'.log', $this->loggers)) {
            return $logger = $this->loggers[$this->defaultLogName.'.log'];		//DONE shouldn't be 'opus.log'
        } else {
            return $this->createDefaultLog('default', 'opus.log');
        }
    }

    /**
     * @param $defaultLogName String
     * @param $defaultLogFileName String
     * @return \Zend_Log
     *
     *
     * To set the default log
     */
    public function createDefaultLog($defaultLogName, $defaultLogFileName)
    {
        $this->defaultLogName = $defaultLogName;
        $this->createLog($defaultLogName, $defaultLogFileName);
        return $this->addLog();
    }

    /**
     * @param null $logger \Zend_Log
     * @return \Zend_Log
     *
     * To add log from external modules
     */
    public function addLog($logger = null)		//should include addLog($name, $logger)
    {
        if ($logger == null) {
            $logger = $this->createLogger();
        }

        $logLevel = $this->getDefaultLogLevel($logger);

        \Zend_Registry::set('LOG_LEVEL', $logLevel);

        $this->loggers[$this->logFileName] = $logger;
        return $logger;
    }

    /**
     * @param $logger \Zend_Log
     * @return mixed
     */
    public function getDefaultLogLevel($logger)		//refactor to return default log level as string and crete applyDefaultLogLevel
    {
    	$config = $this->getConfig();
        if (isset($config->logging->log->level)) {		//DONE use $config = $this->getConfig()
            $logLevelName = $config->logging->log->level;
        } else {
            $logLevelName = 'INFO';
            $logger->warn("Log level not configured, using '" . $logLevelName . "'.");
        }

        $zendLogRefl = new \ReflectionClass('Zend_Log');

        $invalidLogLevel = false;

        $logLevel = $zendLogRefl->getConstant($logLevelName);

        if (empty($logLevel)) {
            $logLevel = Zend_Log::INFO;
            $invalidLogLevel = true;
        }

        $this->createLogLevelFilter($logger, $logLevel);

        if ($invalidLogLevel) {
            $logger->err("Invalid log level '" . $logLevelName .
                "' configured.");
        }

        return $logLevel;
    }

    /**
     * @param $logger \Zend_Log
     * @param $logLevel
     * @throws \Zend_Log_Exception
     */
    protected function createLogLevelFilter($logger, $logLevel)		// create the filter not add to logger
    {
        $priorityFilter = new \Zend_Log_Filter_Priority($logLevel);
        $logger->addFilter($priorityFilter);
    }

    /**
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
                $this->createLog($logName);
                return $this->addLog();
            } catch (Exception $e) {
                throw new Exception('Cannot create new log', 1);		//should be unknown logger
            }
        }
    }

    /**
     * @return \Zend_Log
     * @throws \Zend_Log_Exception
     */
    protected function createLogger()		//need params
    {
        if (isset($this->config->logging->log->translation->file->format)) {
            $format = $this->config->logging->log->translation->file->format;
        } elseif ($this->defaultFormat == null) {
            $format = $this->getDefaultFormat();
        } else {
            $format = $this->defaultFormat;
        }

        $formatter = new \Zend_Log_Formatter_Simple($format);
        $writer = new \Zend_Log_Writer_Stream($this->logFile);
        $writer->setFormatter($formatter);

        $logger = new \Zend_Log($writer);
        fclose($this->logFile);
        return $logger;
    }

    /**
     * @param null|string $defaultFormat Format of logging
     * @return string|null
     */
    public function setFormat($format = null)		//should be setDefaultFormat and use variable in getdefaultFormat()
    {
        $this->defaultFormat = $format;
        return $this->defaultFormat;
    }

    /**
     * @return string
     */
    public function getDefaultFormat(		//use placeholder for the ID string
    {
        $GLOBALS['id_string'] = uniqid(); // Write ID string to global variables, so we can identify/match individual runs.

        return '%timestamp% %priorityName% (%priority%, ID '.$GLOBALS['id_string'].'): %message%' . PHP_EOL;
    }

    //write generic functions for testing
}
