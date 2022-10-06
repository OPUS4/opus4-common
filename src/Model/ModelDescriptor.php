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

namespace Opus\Common\Model;

use Opus\Common\Config;
use Opus\Common\ConfigTrait;

use function array_keys;
use function ucfirst;

class ModelDescriptor implements ModelDescriptorInterface
{
    use ConfigTrait;

    private $modelId;

    private $fields;

    private static $fieldDescriptorClass;

    /**
     * @param string     $modelId
     * @param array|null $config
     */
    public function __construct($modelId, $config = null)
    {
        $this->modelId = $modelId;

        if ($config === null) {
            return; // TODO what should default configuration be?
        }

        $fieldDescriptorClass = self::getFieldDescriptorClass();

        // TODO move to function
        if (isset($config['fields'])) {
            $fields = [];

            foreach ($config['fields'] as $fieldName => $fieldConfig) {
                $field                     = new $fieldDescriptorClass($fieldName, $fieldConfig, $this);
                $fields[$field->getName()] = $field; // Using getName() makes sure upper case first letter is used
            }

            $this->fields = $fields;
        }
    }

    /**
     * @return string
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Returns descriptor for model field.
     *
     * @param string $fieldName Name of field (automatically applies upper case to first letter)
     * @return FieldDescriptor|null
     *
     * TODO throw exception for unknown field?
     */
    public function getFieldDescriptor($fieldName)
    {
        $ucName = ucfirst($fieldName);

        if (isset($this->fields[$ucName])) {
            return $this->fields[$ucName];
        } else {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * @return string
     */
    public static function getFieldDescriptorClass()
    {
        if (self::$fieldDescriptorClass !== null) {
            return self::$fieldDescriptorClass;
        } else {
            $config = Config::get();
            if (isset($config->model->fieldDescriptorClass)) {
                self::$fieldDescriptorClass = $config->model->fieldDescriptorClass;
                return self::$fieldDescriptorClass;
            }
        }

        return FieldDescriptor::class;
    }

    /**
     * @param string $className
     */
    public static function setFieldDescriptorClass($className)
    {
        self::$fieldDescriptorClass = $className;
    }
}
