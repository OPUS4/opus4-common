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
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common;

use Opus\Common\Config\ConfigException;
use Opus\Common\Model\ModelFactoryInterface;
use Opus\Common\Model\Xml\XmlCacheInterface;

/**
 * Central OPUS 4 class for accessing document data.
 *
 * TODO review design and functions (especially anything that is static)
 */
class Repository
{
    private static $repository;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (self::$repository === null) {
            self::$repository = new static();
        }

        return self::$repository;
    }

    /**
     * @return DocumentFinderInterface
     */
    public function getDocumentFinder()
    {
        $config = $this->getConfig();

        if (isset($config->documentFinderClass)) {
            $finderClass = $config->documentFinderClass;
        } else {
            throw new ConfigException('Missing configuration parameter: documentFinderClass');
        }

        return new $finderClass();
    }

    /**
     * @return XmlCacheInterface|null
     *
     * TODO move out of Repository and maybe Common?
     */
    public function getDocumentXmlCache()
    {
        $config = $this->getConfig();

        if (isset($config->documentXmlCacheClass)) {
            $cacheClass = $config->documentXmlCacheClass;
        } else {
            return null;
        }

        return new $cacheClass();
    }

    /**
     * @return ModelFactoryInterface|null
     */
    public function getModelFactory()
    {
        $config = $this->getConfig();

        if (isset($config->modelFactory)) {
            $modelFactoryClass = $config->modelFactory;
        } else {
            throw new ConfigException('Missing configuration parameter: modelFactory');
        }

        return new $modelFactoryClass();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return Config::get();
    }
}
