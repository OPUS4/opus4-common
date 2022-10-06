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

class CollectionRole extends AbstractModel
{
    public const FIELD_NAME            = 'Name';
    public const FIELD_OAI_NAME        = 'OaiName';
    public const FIELD_POSITION        = 'Position';
    public const FIELD_ROOT_COLLECTION = 'RootCollection';
    public const FIELD_LANGUAGE        = 'Language';

    public const FIELD_VISIBLE                = 'Visible';
    public const FIELD_VISIBLE_BROWSING_START = 'VisibleBrowsingStart';
    public const FIELD_VISIBLE_FRONTDOOR      = 'VisibleFrontdoor';
    public const FIELD_VISIBLE_OAI            = 'VisibleOai';

    public const FIELD_DISPLAY_BROWSING       = 'DisplayBrowsing';
    public const FIELD_DISPLAY_FRONTDOOR      = 'DisplayFrontdoor';
    public const FIELD_HIDE_EMPTY_COLLECTIONS = 'HideEmptyCollections';

    public const FIELD_IS_CLASSIFICATION = 'IsClassification';

    public const FIELD_ASSIGN_ROOT        = 'AssignRoot';
    public const FIELD_ASSIGN_LEAVES_ONLY = 'AssignLeavesOnly';

    /**
     * @return CollectionRoleInterface[]
     */
    public static function fetchAll()
    {
        $collectionRoles = self::getModelRepository();
        return $collectionRoles->fetchAll();
    }

    /**
     * @param string $name
     * @return CollectionRoleInterface
     */
    public static function fetchByName($name)
    {
        $collectionRoles = self::getModelRepository();
        return $collectionRoles->fetchByName($name);
    }

    /**
     * @param string $oaiName
     * @return CollectionRoleInterface
     */
    public static function fetchByOaiName($oaiName)
    {
        $collectionRoles = self::getModelRepository();
        return $collectionRoles->fetchByOaiName($oaiName);
    }

    /**
     * @return CollectionRoleInterface[]
     */
    public static function fetchAllOaiEnabledRoles()
    {
        $collectionRoles = self::getModelRepository();
        return $collectionRoles->fetchAllOaiEnabledRoles();
    }

    /**
     * @param string $oaiSetName
     * @return int[]
     */
    public static function getDocumentIdsInSet($oaiSetName)
    {
        $collectionRoles = self::getModelRepository();
        return $collectionRoles->getDocumentIdsInSet($oaiSetName);
    }

    public static function fixPositions()
    {
        $collectionRoles = self::getModelRepository();
        $collectionRoles->fixPositions();
    }

    /**
     * @return int
     */
    public static function getLastPosition()
    {
        $collectionRoles = self::getModelRepository();
        return $collectionRoles->getLastPosition();
    }

    /**
     * @return array
     */
    protected static function loadModelConfig()
    {
        return [
            'fields' => [
                self::FIELD_NAME                   => [
                    'required' => true,
                ],
                self::FIELD_OAI_NAME               => [
                    'required' => true,
                ],
                self::FIELD_POSITION               => [
                    'type' => 'int',
                ],
                self::FIELD_VISIBLE                => [
                    'type' => 'bool',
                ],
                self::FIELD_VISIBLE_BROWSING_START => [
                    'type' => 'bool',
                ],
                self::FIELD_VISIBLE_FRONTDOOR      => [
                    'type' => 'bool',
                ],
                self::FIELD_VISIBLE_OAI            => [
                    'type' => 'bool',
                ],
                self::FIELD_DISPLAY_BROWSING       => [],
                self::FIELD_DISPLAY_FRONTDOOR      => [],
                self::FIELD_IS_CLASSIFICATION      => [
                    'type' => 'bool',
                ],
                self::FIELD_ASSIGN_ROOT            => [],
                self::FIELD_ASSIGN_LEAVES_ONLY     => [],
                self::FIELD_ROOT_COLLECTION        => [
                    'type' => CollectionInterface::class,
                ],
                self::FIELD_HIDE_EMPTY_COLLECTIONS => [
                    'type' => 'bool',
                ],
                self::FIELD_LANGUAGE               => [],
            ],
        ];
    }
}
