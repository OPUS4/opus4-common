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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Log;

use Opus\Exception;
use Opus\Log;

/**
 * Class for managing multiple loggers.
 *
 * The LogService centralizes creating new logs for OPUS 4. During bootstrapping
 * the default logger is created and can be obtained with `getDefaultLog` later.
 *
 * It is possible to configure loggers in the global configuration. A configured
 * logger can be obtained using a NAME.
 *
 * logging.log.[NAME].format
 * logging.log.[NAME].file
 * logging.log.[NAME].level
 *
 * The default values are configured using the old parameters.
 *
 * log.format
 * log.level
 *
 * The default file for a logger is the NAME of the logger with the extension `.log`.
 * Normally the default log output would go into `default.log`, however the name
 * is being customized during bootstrapping, so it becomes `opus.log` or
 * `opus-console.log` for regular runs of OPUS 4.
 *
 * @package     Opus\Log
 *
 * TODO we should configure the default options the same way like for other loggers
 *      logging.log.default.format instead of 'log.format', but I would leave a decision until the end
 * TODO should logger names be case insensitive (?)
 * TODO protection against creating multiple loggers for same file (?)
 * TODO protection against adding loggers for existing name (?)
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

    /** @var LogService Singleton instance of LogService. */
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
     * @return LogService
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
     * @param null|string $priority Name of log level.
     * @param null|string $format Format for log output.
     * @param null|string $filename Optional name of the log file.
     * @return \Zend_Log
     *
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
     * @throws Exception
     */
    public function addLog($name, $logger)
    {
        if (! $logger instanceof \Zend_Log) {
            throw new Exception('Logger added must be of type Zend_Log.');
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
     * @return \Zend_Log
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
     * Returns configuration for a log.
     *
     * If the log has not been configured specifically in the global configuration,
     * default values are used.
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
     * Returns the name of the default log priority.
     *
     * @return string
     */
    public function getDefaultPriorityAsString()
    {
        return $this->convertPriorityToString($this->getDefaultPriority());
    }

    /**
     * Sets the default log priority.
     *
     * Either the name or the Zend_Log constants can be used to set the default
     * log level.
     *
     * @param int|string $priority Value or name of log level.
     *
     */
    public function setDefaultPriority($priority)
    {
        if (is_int($priority)) {
            $this->defaultPriority = $priority;
        } elseif (is_string($priority)) {
            $this->defaultPriority = $this->convertPriorityFromString($priority);
        } else {
            throw new Exception('Setting default priority with invalid parameter.');
        }
    }

    /**
     * Return the default log priority.
     *
     * @return int
     */
    public function getDefaultPriority()
    {
        if ($this->defaultPriority === null) {
            $priority = self::DEFAULT_PRIORITY;

            $config = $this->getConfig();

            if (isset($config->log->level)) {
                $priority = $config->log->level;
            }

            $priorityValue = $this->convertPriorityFromString($priority);

            if ($priorityValue === null) {
                $priorityValue = $this->convertPriorityFromString(self::DEFAULT_PRIORITY);
            }

            $this->defaultPriority = $priorityValue;
        }

        return $this->defaultPriority;
    }

    /**
     * Get a log or create one if not already exists.
     *
     * @param null|string $name Name of log
     * @return \Zend_Log
     */
    public function getLog($name = null)
    {
        if ($name === null || $name == self::DEFAULT_LOG) {
            return $this->getDefaultLog();
        }

        if (array_key_exists($name, $this->loggers)) {
            return $this->loggers[$name];
        } else {
            return $this->createLog($name);
        }
    }

    /**
     * Creates a new, configured Zend_Log object.
     *
     * @param string $format
     * @param int $priority
     * @param string $file
     * @return \Zend_Log
     * @throws \Zend_Log_Exception
     */
    protected function createLogger($format, $priority, $file)
    {
        $preparedFormat = $this->prepareFormat($format);
        $formatter = new \Zend_Log_Formatter_Simple($preparedFormat);

        $writer = new \Zend_Log_Writer_Stream($file);
        $writer->setFormatter($formatter);

        $logger = new Log($writer);
        $logger->setLevel($priority);

        return $logger;
    }

    /**
     * Sets the default log output format.
     *
     * @param string $format Format of log output.
     */
    public function setDefaultFormat($format)
    {
        $this->defaultFormat = $format;
    }

    /**
     * Returns the default log output format.
     *
     * The output format can be set explicitly in this class or
     * be configured in the global configuration.
     *
     * A run ID is added to the output format if the placeholder %runId%
     * is present in order to match log output to separate requests.
     *
     * @return string
     */
    public function getDefaultFormat()
    {
        if ($this->defaultFormat == null) {
            $config = $this->getConfig();

            $format = self::DEFAULT_FORMAT;

            if (isset($config->log->format)) {
                $format = $config->log->format;
            }

            $this->defaultFormat = $format;
        }

        return $this->defaultFormat;
    }

    /**
     * Add RunId to format string.
     *
     * @param $format string
     * @returns string
     */
    public function prepareFormat($format)
    {
        if ($format === null) {
            throw new \InvalidArgumentException('Format must not be null.');
        }

        $runId = $this->getRunId();
        return rtrim(preg_replace('/%runId%/', $runId, $format), PHP_EOL) . PHP_EOL;
    }

    /**
     * Set the run ID to identify/match individual runs.
     * @param string $runId
     */
    public function setRunId($runId)
    {
        $this->runId = $runId;
    }

    /**
     * Get unique run ID.
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
