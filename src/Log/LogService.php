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
 * @category    Framework
 * @package     Opus_Bootstrap
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2008-2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Log;

/**
 * Class to manage multiple loggers
 *
 * @package     Opus_Log
 *
 */
class LogService
{

//DONE add comments for all variable -- use @var
	/** @var Zend_Log $loggers */ 
    public $loggers = [];		//all log objects with lognames as keys	
    private $logFile;		//file pointer resource to the logfile
    public $format;		//format of logging
    private $config;	//Zend_Config
    private $logFilePath = null;	//path to the logfile
    private static $instance;	//log instance of the class
    public $logFileName;	//name of the log file

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function openLog($logFileName = null)		//change to createLog('default', logFileName)
    {
        $this->getConfig();

        $logfilePath = $this->logFilePath == null ? $this->setPath($logFileName) : $this->logFilePath;

        if ($logFileName == null) {
        	$this->logFileName = 'opus.log'
        } else {
        	$this->logFileName = $logFileName;
        }
        $this->logFile = @fopen($logfilePath, 'a', false);
        if ($this->logFile === false) {
            $path = dirname($logfilePath);

            if (! is_dir($path)) {
                throw new Exception('Directory for logging does not exist');
            } else {
                throw new Exception('Failed to open logging file:' . $logfilePath);
            }
        }
    }

    public function getConfig()		//DONE not going to work
    {
        $this->config = \Zend_Registry::get('Zend_Config');
    }

    public function getPath()
    {
        return $this->logFilePath;
    }

    public function setPath($logFileName, $logPath = null)
    {
        if ($logPath == null) {
            $this->logFilePath  = $this->config->workspacePath . '/log/' . $logFileName;
        } else {
            $this->logFilePath = $logPath . $logFileName;
        }
        return $this->logFilePath;
    }

    public function getDefaultLog()
    {
        if (array_key_exists('default.log', $this->loggers)) {
            return $logger = $this->loggers['default.log'];
        } else {
            return $this->setDefaultLog('default.log');
        }
    }

    public function setDefaultLog($logFileName)
    {
        $this->openLog('Zend_Config', $logFileName);
        return $this->addLog();
    }

    public function addLog($logger = null)		//Done change to addLog (shouldn't be necessary)
    {
        if ($logger == null) {
            $logger = $this->createLog();
        }

        if ($this->checkLogLevel() == false) {
            $logLevelName = 'INFO';
            $logger->warn('Log level not configured, using \'' . $logLevelName . '\'.');
        } else {
            $logLevelName = $this->checkLogLevel();
        }

        $zendLogRefl = new \ReflectionClass('Zend_Log');

        $invalidLogLevel = false;

        $logLevel = $zendLogRefl->getConstant($logLevelName);

        if (empty($logLevel)) {
            $logLevel = Zend_Log::INFO;
            $invalidLogLevel = true;
        }

        // filter log output
        $priorityFilter = new \Zend_Log_Filter_Priority($logLevel);
        \Zend_Registry::set('LOG_LEVEL', $logLevel);
        $logger->addFilter($priorityFilter);

        if ($invalidLogLevel) {
            $logger->err('Invalid log level \'' . $logLevelName .
                '\' configured.');
        }

        $this->loggers[$this->logFileName] = $logger;      //how to know the logName for key value loggers other than logFileName
        return $logger;
    }

    public function getLog($logName)		
    {
        if (array_key_exists($logName, $this->loggers)) {
            return $logger = $this->loggers[$logName];
        } else {
            try {
                $this->openLog('Zend_Config', $logName);
                return $this->addLog();
            } catch {
                throw new Exception("Cannot create new log", 1);

            }
        }
    }

    public function checkLogLevel()
    {
        return isset($this->config->logging->log->level) ? $this->config->logging->log->level : false;
    }

    protected function createLog()
    {
        if ($this->format == null) {
            $this->setDefaultFormat();
        }

        $formatter = new \Zend_Log_Formatter_Simple($this->format);

        $writer = new \Zend_Log_Writer_Stream($this->logFile);
        $writer->setFormatter($formatter);

        $logger = new \Zend_Log($writer);
        return $logger;
    }

    public function setDefaultFormat($format = null)		//DONE rename to setDefaultFormat()
    {
        $GLOBALS['id_string'] = uniqid(); // Write ID string to global variables, so we can identify/match individual runs.

        $format = isset($this->config->logging->log->translation->file->format) ? $this->config->logging->log->translation->file->format :
            '%timestamp% %priorityName% (%priority%, ID '.$GLOBALS['id_string'].'): %message%' . PHP_EOL;       //Done seperate function

        $this->format = $format;
        return $this->format;
    }

    public function getDefaultFormat()
    {
    	return $this->format;
    }
}
