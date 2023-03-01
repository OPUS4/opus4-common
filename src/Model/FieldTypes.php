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

use function array_diff;
use function class_implements;
use function in_array;
use function scandir;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * TODO create instance -> getInstance()
 * TODO use LoggingTrait instead of static Log::get()
 * TODO single function for determining class for type
 */
class FieldTypes
{
    public const TYPES_NAMESPACE = 'Opus\Common\Model\FieldType';

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
        $files  = array_diff(scandir(
            __DIR__ . DIRECTORY_SEPARATOR . 'FieldType'
        ), ['.', '..']);
        $result = [];

        if ($files === false) {
            return $result;
        }

        foreach ($files as $file) {
            if (substr($file, strlen($file) - 4) === '.php') {
                // found PHP file - try to instantiate TODO getting class for type should be separate function
                $className  = self::TYPES_NAMESPACE . '\\' . substr($file, 0, strlen($file) - 4);
                $interfaces = class_implements($className);
                if (in_array(FieldTypeInterface::class, $interfaces)) {
                    $type = new $className();
                    if (! $rawNames) {
                        $typeName          = $type->getName();
                        $result[$typeName] = $typeName;
                    } else {
                        $result[] = $className;
                    }
                }
            }
        }

        return $result;
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
        if ($type === null || empty($type)) {
            return null;
        }

        $typeClass = self::TYPES_NAMESPACE . '\\' . $type;

        try {
            $typeObj = new $typeClass();
        } catch (Throwable $ex) {
            Log::get()->err('could not find enrichment type class ' . $typeClass);
            return null;
        }

        return $typeObj;
    }
}
