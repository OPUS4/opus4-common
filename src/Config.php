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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common;

use InvalidArgumentException;
use Zend_Config;
use Zend_Exception;

use function count;
use function explode;
use function strlen;
use function substr;
use function trim;

use const DIRECTORY_SEPARATOR;

class Config
{
    use LoggingTrait;

    /** @var string */
    private $tempPath;

    /** @var string[] */
    private $availableLanguages;

    /** @var static */
    private static $instance;

    /** @var Zend_Config */
    private static $config;

    /**
     * Returns the path to the application workspace.
     *
     * @return string
     * @throws OpusException
     */
    public function getWorkspacePath()
    {
        $config = $this->get();

        if (! isset($config->workspacePath)) {
            $this->getLogger()->err('missing config key workspacePath');
            throw new OpusException('missing configuration key workspacePath');
        }

        $workspacePath = $config->workspacePath;

        if (substr($workspacePath, -1) === DIRECTORY_SEPARATOR) {
            return $workspacePath;
        } else {
            return $config->workspacePath . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Returns path to temporary files folder.
     *
     * @return string Path for temporary files.
     * @throws OpusException
     */
    public function getTempPath()
    {
        if ($this->tempPath === null) {
            $this->tempPath = trim($this->getWorkspacePath() . 'tmp' . DIRECTORY_SEPARATOR);
        }

        return $this->tempPath;
    }

    /**
     * Set path to folder for temporary files.
     *
     * @param string $tempPath
     */
    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }

    /**
     * @return string[]
     */
    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }

    /**
     * @param string[] $availableLanguages
     */
    public function setAvailableLanguages($availableLanguages)
    {
        $this->availableLanguages = $availableLanguages;
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    /**
     * @param self|null $config
     */
    public static function setInstance($config)
    {
        if ($config !== null && ! $config instanceof Config) {
            throw new InvalidArgumentException('Argument must be instance of Opus\Config or null');
        }

        self::$instance = $config;
    }

    /**
     * @param Zend_Config $config
     */
    public static function set($config)
    {
        self::$config = $config;
    }

    /**
     * @return Zend_Config
     */
    public static function get()
    {
        return self::$config;
    }

    /**
     * Gets a value from a Zend_Config object.
     *
     * @param Zend_Config $config
     * @param string      $option
     * @return null|Zend_Config
     */
    public static function getValueFromConfig($config, $option)
    {
        if ($option === null || strlen(trim($option)) === 0) {
            return null;
        }

        $keys      = explode('.', $option);
        $subconfig = $config;
        foreach ($keys as $key) {
            $subconfig = $subconfig->get($key);
            if (! $subconfig instanceof Zend_Config) {
                break;
            }
        }
        return $subconfig;
    }

    /**
     * Updates a value in a Zend_Config object.
     *
     * @param Zend_Config $config
     * @param string      $option Name of option
     * @param string      $value New value for option
     * @throws Zend_Exception
     * TODO review and if possible replace this code with something simpler
     */
    public static function setValueInConfig($config, $option, $value)
    {
        if ($config->readOnly()) {
            Log::get()->err('Zend_Config object is readonly.');
            return;
        }

        $keys = explode('.', $option);

        $subconfig = $config;

        $index = 0;

        foreach ($keys as $key) {
            $index++;
            if ($subconfig->get($key) === null && $index < count($keys)) {
                // create subsection
                eval('$subconfig->' . $key . ' = array();');
                $subconfig = $subconfig->get($key);
            } else {
                // set value
                eval('$subconfig->' . $key . ' = $value;');
            }
        }
    }
}
