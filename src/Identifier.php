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

use Opus\Common\Model\AbstractModel;

class Identifier extends AbstractModel
{
    public const FIELD_VALUE = 'Value';
    public const FIELD_TYPE  = 'Type';

    // TODO These two fields are only used for DOIs - refactor, move out of basic Identifier class
    public const FIELD_STATUS                 = 'Status';
    public const FIELD_REGISTRATION_TIMESTAMP = 'RegistrationTs';

    /**
     * Used to map a identifier type to the name of a Document field.
     *
     * The identifier type 'opus3-id' can be accessed using the Document field 'IdentifierOpus3'. This functions
     * does the mapping.
     *
     * TODO Does this functionality belong here?
     *
     * @param string $type
     * @return string
     */
    public static function getFieldnameForType($type)
    {
        return self::getModelRepository()->getFieldnameForType($type);
    }

    /**
     * @param string $fieldname
     * @return string
     */
    public static function getTypeForFieldname($fieldname)
    {
        return self::getModelRepository()->getTypeForFieldname($fieldname);
    }

    /**
     * @return array[]
     */
    protected static function loadModelConfig()
    {
        return [
            'fields' => [
                self::FIELD_TYPE                   => [],
                self::FIELD_VALUE                  => [],
                self::FIELD_STATUS                 => [],
                self::FIELD_REGISTRATION_TIMESTAMP => [],
            ],
        ];
    }
}
