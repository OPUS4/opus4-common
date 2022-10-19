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

class Language extends AbstractModel
{
    public const FIELD_PART_2B  = 'Part2B';
    public const FIELD_PART_2T  = 'Part2T';
    public const FIELD_PART1    = 'Part1';
    public const FIELD_SCOPE    = 'Scope';
    public const FIELD_TYPE     = 'Type';
    public const FIELD_REF_NAME = 'RefName';
    public const FIELD_COMMENT  = 'Comment';
    public const FIELD_ACTIVE   = 'Active';

    /**
     * @return LanguageInterface[]
     */
    public static function getAll()
    {
        return self::getLanguageRepository()->getAll();
    }

    /**
     * @return LanguageInterface[]
     */
    public static function getAllActive()
    {
        return self::getLanguageRepository()->getAllActive();
    }

    /**
     * @return LanguageInterface[]
     */
    public static function getUsedLanguages()
    {
        return self::getLanguageRepository()->getUsedLanguages();
    }

    /**
     * @param string      $lang
     * @param null|string $part
     * @return string
     */
    public static function getLanguageCode($lang, $part = null)
    {
        return self::getLanguageRepository()->getLanguageCode($lang, $part);
    }

    /**
     * @param string $locale
     * @return string
     */
    public static function getPart2tForPart1($locale)
    {
        return self::getLanguageRepository()->getPart2tForPart1($locale);
    }

    /**
     * @return array
     */
    public static function getAllActiveTable()
    {
        return self::getLanguageRepository()->getAllActiveTable();
    }

    /**
     * @param string $code
     * @return array
     */
    public static function getPropertiesByPart2T($code)
    {
        return self::getLanguageRepository()->getPropertiesByPart2T($code);
    }

    /**
     * @return array[]
     */
    protected static function loadModelConfig()
    {
        return [
            'fields' => [
                self::FIELD_ACTIVE   => [
                    'type' => 'bool',
                ],
                self::FIELD_COMMENT  => [],
                self::FIELD_PART1    => [],
                self::FIELD_PART_2B  => [],
                self::FIELD_PART_2T  => [],
                self::FIELD_REF_NAME => [],
                self::FIELD_SCOPE    => [], // TODO limit allowed values
                self::FIELD_TYPE     => [], // TODO limit allowed values
            ],
        ];
    }

    /**
     * @return LanguageRepositoryInterface
     */
    public static function getLanguageRepository()
    {
        return self::getModelRepository();
    }
}
