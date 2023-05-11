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
use Opus\Common\Model\NotFoundException;

class Collection extends AbstractModel
{
    public const FIELD_DISPLAY_FRONTDOOR = 'DisplayFrontdoor';
    public const FIELD_NAME              = 'Name';
    public const FIELD_NUMBER            = 'Number';
    public const FIELD_OAI_SUBSET        = 'OaiSubset';
    public const FIELD_VISIBLE           = 'Visible';
    public const FIELD_VISIBLE_PUBLISH   = 'VisiblePublish';

    /**
     * Removes document from current collection by deleting from the relation
     * table "link_documents_collections".
     *
     * @param null|int $docId
     * @return int
     */
    public static function unlinkCollectionsByDocumentId($docId = null)
    {
        return self::getModelRepository()->unlinkCollectionsByDocumentId($docId);
    }

    /**
     * Returns all collection for given (role_id) as array
     * with Opus\Collection objects.  Always returning an array, even if the
     * result set has zero or one element.
     *
     * @param  int $roleId
     * @return CollectionInterface[].
     */
    public function fetchCollectionsByRoleId($roleId)
    {
        return self::getModelRepository()->fetchCollectionsByRoleId($roleId);
    }

    /**
     * @param int    $roleId
     * @param string $number
     * @return CollectionInterface[]
     */
    public static function fetchCollectionsByRoleNumber($roleId, $number)
    {
        return self::getModelRepository()->fetchCollectionsByRoleNumber($roleId, $number);
    }

    /**
     * @param int    $roleId
     * @param string $name
     * @return CollectionInterface[]
     */
    public static function fetchCollectionsByRoleName($roleId, $name)
    {
        return self::getModelRepository()->fetchCollectionsByRoleName($roleId, $name);
    }

    /**
     * @param int $docId
     * @return int[]
     */
    public static function fetchCollectionIdsByDocumentId($docId)
    {
        return self::getModelRepository()->fetchCollectionIdsByDocumentId($docId);
    }

    /**
     * @param string         $term
     * @param null|int|int[] $roles
     * @return array
     */
    public static function find($term, $roles = null)
    {
        return self::getModelRepository()->find($term, $roles);
    }

    /**
     * @return array
     */
    protected static function loadModelConfig()
    {
        return []; // TODO implement
    }

    /**
     * Creates collection object from data in array.
     *
     * If array contains 'Id' the corresponding existing collection is used.
     *
     * TODO If 'Id' is from a different system the wrong collection might be used.
     *      How can we deal with the possible problems? Does it make more sense to
     *      handle reuse of existing objects outside the fromArray function?
     *      Would it make more sense if we generte new objects and then apply
     *      another function that maps the attributes of a document to existing
     *      objects in the database. It seems this really depends on the type of
     *      object in question.
     *
     * TODO Collections should probably never be created as part of an import. When
     *      a document is stored the connected collections should already exist. If
     *      not the storing operation should fail.
     *
     * @param array $data
     * @return CollectionInterface|null
     */
    public static function fromArray($data)
    {
        $col = null;

        if (isset($data['Id'])) {
            try {
                $col = self::get($data['Id']);

                // TODO update from array not supported (handling of roleId)
                // $col->updateFromArray($data);
            } catch (NotFoundException $omnfe) {
                // TODO handle it
            }
        }

        if ($col === null) {
            $col = parent::fromArray($data);
        }

        return $col;
    }
}
