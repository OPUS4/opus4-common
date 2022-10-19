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

class File extends AbstractModel
{
    public const FIELD_COMMENT               = 'Comment';
    public const FIELD_FILE_SIZE             = 'FileSize';
    public const FIELD_HASH_VALUE            = 'HashValue';
    public const FIELD_LABEL                 = 'Label';
    public const FIELD_LANGUAGE              = 'Language';
    public const FIELD_MIME_TYPE             = 'MimeType';
    public const FIELD_PATH_NAME             = 'PathName';
    public const FIELD_SERVER_DATE_SUBMITTED = 'ServerDateSubmitted';
    public const FIELD_SORT_ORDER            = 'SortOrder';
    public const FIELD_VISIBLE_IN_FRONTDOOR  = 'VisibleInFrontdoor';
    public const FIELD_VISIBLE_IN_OAI        = 'VisibleInOai';
    // TODO TempFile

    /**
     * @param int    $docId
     * @param string $pathName
     * @return FileInterface|null
     */
    public static function fetchByDocIdPathName($docId, $pathName)
    {
        return self::getModelRepository()->fetchByDocIdPathName($docId, $pathName);
    }

    /**
     * @return array
     */
    protected static function loadModelConfig()
    {
        return [
            'fields' => [
                self::FIELD_COMMENT               => [],
                self::FIELD_FILE_SIZE             => [
                    'type' => 'int',
                ],
                self::FIELD_HASH_VALUE            => [],
                self::FIELD_LABEL                 => [],
                self::FIELD_LANGUAGE              => [],
                self::FIELD_MIME_TYPE             => [],
                self::FIELD_PATH_NAME             => [
                    'required' => true,
                ],
                self::FIELD_SERVER_DATE_SUBMITTED => [
                    'type' => Date::class,
                ],
                self::FIELD_SORT_ORDER            => [
                    'type' => 'int',
                ],
                self::FIELD_VISIBLE_IN_FRONTDOOR  => [
                    'type' => 'bool',
                ],
                self::FIELD_VISIBLE_IN_OAI        => [
                    'type' => 'bool',
                ],
            ],
        ];
    }
}
