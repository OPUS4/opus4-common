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
 * @package     Opus
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus;

/**
 * Class Config
 * @package Opus
 *
 * TODO revisit using get() for Laminas\Config object
 *      should Opus\Config extend Laminas\Config?
 * TODO replace singleton by dependency injection in Laminas
 * TODO in general review this stuff and think about it !!!
 */
class Config
{
    use LoggingTrait;

    private $tempPath;

    private $availableLanguages;

    private static $instance;

    private static $config;

    /**
     * Returns the path to the application workspace.
     *
     * @throws Exception
     */
    public function getWorkspacePath()
    {
        $config = $this->get();

        if (! isset($config->workspacePath)) {
            $this->getLogger()->err('missing config key workspacePath');
            throw new Exception('missing configuration key workspacePath');
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
     * @return string Path for temporary files.
     * @throws Exception
     */
    public function getTempPath()
    {
        if (is_null($this->tempPath)) {
            $this->tempPath = trim($this->getWorkspacePath() . 'tmp' . DIRECTORY_SEPARATOR);
        }

        return $this->tempPath;
    }

    /**
     * Set path to folder for temporary files.
     * @param $tempPath
     */
    public function setTempPath($tempPath)
    {
        $this->tempPath = $tempPath;
    }

    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }

    public function setAvailableLanguages($availableLanguages)
    {
        $this->availableLanguages = $availableLanguages;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    public static function setInstance($config)
    {
        if ($config !== null && ! $config instanceof Config) {
            throw new \InvalidArgumentException('Argument must be instance of Opus\Config or null');
        }

        self::$instance = $config;
    }

    public static function set($config)
    {
        self::$config = $config;
    }

    public static function get()
    {
        return self::$config;
    }
}
