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
use Opus\Common\Config\DocumentTypesInterface;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\ModelFactoryInterface;
use Opus\Common\Model\ModelInterface;
use Opus\Common\Model\ModelRepositoryInterface;
use Opus\Common\Model\Xml\XmlCacheInterface;
use Zend_Config;

use function class_exists;
use function class_implements;
use function in_array;
use function strrpos;
use function substr;

/**
 * Central OPUS 4 class for accessing document data.
 *
 * TODO review design and functions (especially anything that is static)
 * TODO cache ModelFactory?
 * TODO use class as type?
 * TODO converting class to type, i.e. Opus\Common\Document to Document means a flat naming structure - okay?
 */
class Repository
{
    /** @var static */
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
     * @return DocumentTypesInterface
     */
    public function getDocumentTypes()
    {
        $documentTypesClass = '';

        return new $documentTypesClass();
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
     * @param string $type
     * @return ModelRepositoryInterface
     *
     * TODO use class as type?
     */
    public function getModelRepository($type)
    {
        if (class_exists($type)) {
            $type = $this->getModelTypeForClass($type);
        }

        $modelFactory = $this->getModelFactory();

        return $modelFactory->getRepository($type);
    }

    /**
     * Returns model type for model class.
     *
     * The mapping from class to type should be centralized here. Packages implementing persistence for the data model
     * should only map from model types to implementing classes. Code using the date model should only use the generic
     * model classes in Opus\Common.
     *
     * TODO is there a need for other packages to extend the data model with new classes?
     *
     * @param string $modelClass
     * @return false|string
     *
     * TODO this works for now, but needs to be reviewed later
     */
    public function getModelTypeForClass($modelClass)
    {
        $interfaces = class_implements($modelClass);

        if (! $interfaces || ! in_array(ModelInterface::class, $interfaces)) {
            throw new ModelException("$modelClass does not implement ModelInterface");
        }

        $pos = strrpos($modelClass, '\\');

        return substr($modelClass, $pos + 1);
    }

    /**
     * @return Zend_Config|null
     */
    public function getConfig()
    {
        return Config::get();
    }
}
