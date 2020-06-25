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
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus/Log;

class LogService extends \Zend_Application_Bootstrap_Bootstrap 
{
	public $logger;
	public $logLevelName;
	public $logLevelNotConfigured;

	public static function getInstance($config){

		// Detect if running in CGI environment.
        if (isset($config->log->filename)) {
            $logFilename = $config->log->filename;
        } else {
            $logFilename = 'opus.log';
            if (! array_key_exists('SERVER_PROTOCOL', $_SERVER)
                and ! array_key_exists('REQUEST_METHOD', $_SERVER)) {
                $logFilename = "opus-console.log";
            }
        }

        $logfilePath = $config->workspacePath . '/log/' . $logFilename;

        $logfile = @fopen($logfilePath, 'a', false);

        if ($logfile === false) {
            $path = dirname($logfilePath);

            if (! is_dir($path)) {
                throw new Exception('Directory for logging does not exist');
            } else {
                throw new Exception('Failed to open logging file:' . $logfilePath);
            }
        }

        $GLOBALS['id_string'] = uniqid(); // Write ID string to global variables, so we can identify/match individual runs.

        $format = '%timestamp% %priorityName% (%priority%, ID '.$GLOBALS['id_string'].'): %message%' . PHP_EOL;
        $formatter = new \Zend_Log_Formatter_Simple($format);

        $writer = new \Zend_Log_Writer_Stream($logfile);
        $writer->setFormatter($formatter);

        $this->logger = new \Zend_Log($writer);
        $this->logLevelName = 'INFO';
        $this->logLevelNotConfigured = false;

        if (isset($config->log->level)) {
            $logLevelName = strtoupper($config->log->level);
        } else {
            $logLevelNotConfigured = true;
        }

        returm $this;

	}

	public function getDefaultLog(){

		$logger = $this->logger;
		$logLevelName = $this->logLevelName;
		$logLevelNotConfigured = $this->logLevelNotConfigured;

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

        if ($logLevelNotConfigured) {
            $logger->warn('Log level not configured, using default \'' . $logLevelName . '\'.');
        }

        if ($invalidLogLevel) {
            $logger->err('Invalid log level \'' . $logLevelName .
                    '\' configured.');
        }

        \Zend_Registry::set('Zend_Log', $logger);

        $logger->debug('Logging initialized');

        return $logger;
	}

	public function getLog($logName){

		$logger = $this->logger;
		$logLevelName = $this->logLevelName;
		$logLevelNotConfigured = $this->logLevelNotConfigured;

		$zendLogRefl = new \ReflectionClass('Zend_Log');

		$invalidLogLevel = false;

        $logLevel = $zendLogRefl->getConstant($logLevelName);

	}


}

