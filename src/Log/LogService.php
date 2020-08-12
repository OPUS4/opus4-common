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

use Opus\Exception;

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
 *
 * TODO provide function for closing loggers (file handles) - basically this should remove the logger
 *      a new call to createLog will create a new logger object
 *
 * TODO should logger names be case sensitive
 */
class LogService
{

    /**
     * Default format used if nothing has been provided or configured.
     */
    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%, ID %runId%): %message%';

    /**
     * Default priority if nothings has been provided or configured.
     */
    const DEFAULT_PRIORITY = 'INFO';

    /**
     * Name of logger used as default.
     */
    const DEFAULT_LOG = 'default';

    /** @var Opus\Log\LogService Singleton instance of LogService. */
    private static $instance;

    /** @var \Zend_Config Global configuration. */
    private $config;

    /** @var string Path to the folder for log files. */
    private $logPath = null;

    /** @var \Zend_Log[] All log objects with log names as keys. */
    private $loggers = [];

    /** @var string Default format of log output. */
    private $defaultFormat;

    /** @var string Marker of current run/request for messages. */
    private $runId;

    /** @var int Default log priority. */
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
     * @param string $name Name of the log.
     * @param null|string $filename string optional name of the log file.
     * @return \Zend_Log
     *
     * TODO are we using configuration here?
     * TODO what if logger already exists (name, file)
     */
    public function createLog($name, $priority = null, $format = null, $filename = null)
    {
        $logConfig = $this->getLogConfig($name);

        if ($filename === null) {
            $filename = $logConfig->file;
        }

        $logFilePath = $this->getPath() . $filename;
        $logFile = @fopen($logFilePath, 'a', false);
        if ($logFile === false) {
            $path = dirname($logFilePath);

            if (! is_dir($path)) {
                throw new \Exception('Directory for logging does not exist');
            } else {
                throw new \Exception('Failed to open logging file:' . $logFilePath);
            }
        }

        if ($priority === null) {
            $priority = $logConfig->level;
        }

        $level = $this->convertPriorityFromString($priority);

        if ($format === null) {
            $format = $logConfig->format;
        }

        $logger = $this->createLogger($format, $level, $logFile);

        $this->addLog($name, $logger);

        return $logger;
    }

    /**
     * To add log from external modules to loggers array.
     *
     * @param string $name Name of log
     * @param \Zend_Log $logger
     *
     *
     * TODO check if logger already exists
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
        if ($this->config === null) {
            $this->config = \Zend_Registry::get('Zend_Config');
        }
        return $this->config;
    }

    /**
     * Sets configuration.
     * @param \Zend_Config $config
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
        if ($this->logPath === null) {
            $config = $this->getConfig();
            if (isset($config->workspacePath)) {
                $this->logPath = $config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
            } else {
                throw new Exception('Workspace path not found in configuration.');
            }
        }

        return $this->logPath;
    }

    /**
     * Set the path to the log folder.
     *
     * The functions adds a directory separator to the end of the string.
     *
     * @param string $logPath
     */
    public function setPath($logPath)
    {
        $this->logPath = rtrim($logPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the default log or creates new default log if one doesn't exist already.
     *
     * @return mixed|Zend_Log
     */
    public function getDefaultLog()
    {
        if (array_key_exists(self::DEFAULT_LOG, $this->loggers)) {
            return $this->loggers[self::DEFAULT_LOG];
        } else {
            return $this->createLog(self::DEFAULT_LOG);
        }
    }

    /**
     * Get a log's configurations.
     *
     * @param string $name
     * @return \Zend_Config
     */
    public function getLogConfig($name)
    {
        $config = $this->getConfig();

        $defaultConfig = new \Zend_Config([
            'format' => $this->getDefaultFormat(),
            'file' => $name . '.log',
            'level' => $this->getDefaultPriorityAsString()
        ], true);

        if (isset($config->logging->log->$name)) {
            return $defaultConfig->merge($config->logging->log->$name);
        } else {
            return $defaultConfig;
        }
    }

    /**
     * Returns the log level.
     *
     * Gets either the log level name from configuration or the default log level name and
     *
     * @return int
     *
     *
     * TODO - $logger could not be defined yet.
     *      May be make class variable flag and check where the function is called to write the warning and error messages.
     */
    public function getDefaultPriorityAsString()
    {
        return $this->convertPriorityToString($this->getDefaultPriority());
    }

    /**
     * Sets the default log priority.
     *
     * @param int|string $priority
     *
     */
    public function setDefaultPriority($priority)
    {
        if (is_int($priority)) {
            $this->defaultPriority = $priority;
        } else if (is_string($priority)) {
            $this->defaultPriority = $this->convertPriorityFromString($priority);
        } else {
            throw new Exception('Setting default priority with invalid parameter.');
        }
    }

    /**
     * Return the default log priority.
     *
     * @return String
     */
    public function getDefaultPriority()
    {
        if ($this->defaultPriority === null) {
            $priority = self::DEFAULT_PRIORITY;

            $config = $this->getConfig();

            if (isset($config->log->level)) {
                $priority = $config->log->level;
                // TODO verify it is a valid priority string (in a separate function)
            }

            $this->defaultPriority = $this->convertPriorityFromString($priority);
        }

        return $this->defaultPriority;
    }

    /**
     * Get a log or create one if not already exists.
     *
     * @param string $name Name of log
     * @return mixed|\Zend_Log
     */
    public function getLog($name = null)
    {
        if ($name === null || $name == self::DEFAULT_LOG) {
            return $this->getDefaultLog();
        }

        if (array_key_exists($name, $this->loggers)) {
            return $logger = $this->loggers[$name];
        } else {
            return $this->createLog($name);
        }
    }

    /**
     * Creates a new, configured Zend_Log object.
     *
     * @param string $logFile
     * @param int $priority
     * @param string $logFile
     * @return \Zend_Log
     * @throws \Zend_Exception
     * @throws \Zend_Log_Exception
     *
     * TODO should checks happen here or at a higher level
     */
    protected function createLogger($format, $priority, $file)
    {
        $formatter = new \Zend_Log_Formatter_Simple($format);

        $writer = new \Zend_Log_Writer_Stream($file);
        $writer->setFormatter($formatter);

        $logger = new \Zend_Log($writer);

        $priorityFilter = new \Zend_Log_Filter_Priority($priority);
        $logger->addFilter($priorityFilter);

        return $logger;
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

    /**
     * Converts priority int into string.
     * @param $priority
     */
    public function convertPriorityToString($priority)
    {
        $zendLogRefl = new \ReflectionClass('Zend_Log');
        $constants = $zendLogRefl->getConstants();

        $levels = array_flip($constants);

        if (isset($levels[$priority])) {
            return $levels[$priority];
        } else {
            return null;
        }
    }

    /**
     * Converts priority string into int.
     * @param $priority
     * @return mixed
     */
    public function convertPriorityFromString($priorityName)
    {
        $zendLogRefl = new \ReflectionClass('Zend_Log');
        $priority = $zendLogRefl->getConstant(strtoupper($priorityName));

        if ($priority !== false) {
            return $priority;
        } else {
            return null;
        }
    }
}
