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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Model;

use Opus\Common\Log;
use Throwable;

use function array_keys;
use function array_values;
use function basename;
use function class_implements;
use function glob;
use function in_array;

use const DIRECTORY_SEPARATOR;

/**
 * TODO create instance -> getInstance()
 * TODO use LoggingTrait instead of static Log::get()
 * TODO IMPORTANT use mapping to determine class for type
 * TODO IMPORTANT classname === typename (at the moment) - THIS IS NOT GUARANTEED
 */
class FieldTypes
{
    public const TYPES_NAMESPACE = 'Opus\Common\Model\FieldType';

    /** @var array|null */
    private static $fieldTypes;

    /**
     * Ermittelt die Klassennamen aller im System verfügbaren EnrichmentTypes.
     *
     * @param bool $rawNames
     * @return array
     *
     * TODO this needs to be configurable, like in Zend for helpers and plugins, classes might not be centralized
     * TODO cache FieldType objects - one per type should be enough
     */
    public static function getAll($rawNames = false)
    {
        if (self::$fieldTypes === null) {
            $files = glob(__DIR__ . DIRECTORY_SEPARATOR . 'FieldType' . DIRECTORY_SEPARATOR . '*.php');

            if ($files === false) {
                self::$fieldTypes = [];
            } else {
                $types = [];

                foreach ($files as $file) {
                    // found PHP file - try to instantiate
                    $fileName  = basename($file, '.php');
                    $className = self::getTypeClass($fileName);

                    $interfaces = class_implements($className);
                    if (! in_array(FieldTypeInterface::class, $interfaces)) {
                        continue;
                    }

                    $type     = new $className();
                    $typeName = $type->getName();

                    $types[$typeName] = $className;
                }

                self::$fieldTypes = $types;
            }
        }

        if ($rawNames) {
            // return type classes
            return array_values(self::$fieldTypes);
        } else {
            // return type names
            return array_keys(self::$fieldTypes);
        }
    }

    /**
     * Gibt ein Objekt des zugehörigen Enrichment-Types zurück, oder null, wenn
     * für den Enrichment-Key kein Typ festgelegt wurde (bei Altdaten) oder der
     * Typ aus einem anderen Grund nicht geladen werden konnte.
     *
     * @param string $type
     * @return FieldTypeInterface|null
     */
    public static function getType($type)
    {
        if ($type === null || empty($type || ! in_array($type, self::getAll()))) {
            return null;
        }

        $typeClass = self::getTypeClass($type);

        // TODO check first with class_exists($typeClass, false) ?
        try {
            $typeObj = new $typeClass();
        } catch (Throwable $ex) { // TODO Throwable only available in PHP 7+
            Log::get()->err('could not find field type class ' . $typeClass, $ex);
            return null;
        }

        return $typeObj;
    }

    /**
     * @param string $type
     * @return string
     *
     * TODO better way? - allow registering namespaces/types like in Zend for form elements?
     * TODO use $fieldTypes name -> class mapping instead of "calculating" classes
     */
    public static function getTypeClass($type)
    {
        return self::TYPES_NAMESPACE . '\\' . $type;
    }
}
