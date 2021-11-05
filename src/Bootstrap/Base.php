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
 * @copyright   Copyright (c) 2008-2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Bootstrap;

use Exception;
use Opus\Config;
use Opus\Log;
use Opus\Log\LogService;
use Zend_Application_Bootstrap_Bootstrap;
use Zend_Cache;
use Zend_Config;
use Zend_Db_Table_Abstract;
use Zend_Locale;
use Zend_Locale_Data;
use Zend_Log;
use Zend_Registry;
use Zend_Translate;

use function array_key_exists;

/**
 * Provide basic workflow of setting up an application.
 *
 * phpcs:disable
 */
class Base extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Override this to do custom backend setup.
     */
    protected function _initBackend()
    {
        $this->bootstrap(['ZendCache', 'OpusLocale', 'Database', 'Logging']);
    }

    /**
     * Initializes the location for temporary files.
     */
    protected function _initTemp()
    {
        $this->bootstrap('Configuration');
        $config        = $this->getResource('Configuration');
        $tempDirectory = $config->workspacePath . '/tmp/';
        Config::getInstance()->setTempPath($tempDirectory);
    }

    /**
     * Setup zend cache directory.
     */
    protected function _initZendCache()
    {
        $this->bootstrap('Configuration');
        $config = $this->getResource('Configuration');

        $frontendOptions = [
            'lifetime'                => 600, // in seconds
            'automatic_serialization' => true,
        ];

        $backendOptions = [
            // Directory where to put the cache files. Must be writeable for
            // application server
            'cache_dir' => $config->workspacePath . '/cache/',
        ];

        if ($this->isConsoleScript()) {
            $backendOptions['file_name_prefix'] = 'zend_cache_console';
        }

        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

        Zend_Translate::setCache($cache);
        Zend_Locale::setCache($cache);
        Zend_Locale_Data::setCache($cache);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

        return $cache;
    }

    /**
     * Load application configuration file and register the configuration
     * object with the Zend registry under 'Zend_Config'.
     * *
     *
     * @throws Exception          Exception is thrown if configuration level is invalid.
     * @return Zend_Config
     */
    protected function _initConfiguration()
    {
        $config = new Zend_Config($this->getOptions());
        Config::set($config);

        return $config;
    }

    /**
     * Setup Logging
     *
     * @throws Exception If logging file couldn't be opened.
     * @return Zend_Log
     *
     * Use LogService API for calling different logs with their names
     * e.g., getLog('opus')
     */
    protected function _initLogging()
    {
        $this->bootstrap('Configuration');

        $config = $this->getResource('Configuration');

        // Detect if running in CGI environment.
        if (isset($config->log->filename)) {
            $logFilename = $config->log->filename;
        } else {
            $logFilename = 'opus.log';
            if ($this->isConsoleScript()) {
                $logFilename = "opus-console.log";
            }
        }

        $logService = LogService::getInstance();

        // TODO could make sure priority is definitely correct by setting it here
        $logger   = $logService->createLog(LogService::DEFAULT_LOG, null, null, $logFilename);
        $logLevel = $logService->getDefaultPriority();

        Log::set($logger);

        $logger->debug('Logging initialized');

        return $logger;
    }

    /**
     * Setup timezone and default locale.
     *
     * Registers locale with key Zend_Locale as mentioned in the ZF documentation.
     */
    protected function _initOpusLocale()
    {
        // Need cache initializatino for Zend_Locale.
        $this->bootstrap('ZendCache');

        // This avoids an exception if the locale cannot be determined automatically.
        // TODO setup in config, still put in registry?
        $locale = new Zend_Locale("de");
        Zend_Registry::set('Zend_Locale', $locale); // TODO switch to Laminas mechanism
    }

    protected function isConsoleScript()
    {
        return ! (array_key_exists('SERVER_PROTOCOL', $_SERVER) or
            array_key_exists('REQUEST_METHOD', $_SERVER));
    }
}
