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

namespace Opus\Common;

/**
 * Interface for collection related functions manipulating multiple documents.
 */
interface CollectionRepositoryInterface
{
    /**
     * Removes document from current collection by deleting from the relation
     * table "link_documents_collections".
     *
     * @param null|int $docId
     */
    public function unlinkCollectionsByDocumentId($docId = null);

    /**
     * @param int    $roleId
     * @param string $name
     * @return CollectionInterface[]
     */
    public function fetchCollectionsByRoleName($roleId, $name);

    /**
     * Returns all collection for given (role_id, collection number) as array
     * with Opus\Collection objects.  Always returning an array, even if the
     * result set has zero or one element.
     *
     * @param  int    $roleId
     * @param  string $number
     * @return CollectionInterface[]
     */
    public function fetchCollectionsByRoleNumber($roleId, $number);

    /**
     * Returns all collection for given (role_id) as array
     * with Opus\Collection objects.  Always returning an array, even if the
     * result set has zero or one element.
     *
     * @param  int $roleId
     * @return CollectionInterface[]
     */
    public function fetchCollectionsByRoleId($roleId);

    /**
     * Returns all collection_ids for a given document_id.
     *
     * @param  int $docId
     * @return array  Array of collection Ids.
     *
     * FIXME: This method belongs to Opus\Db\Link\Documents\Collections
     */
    public function fetchCollectionIdsByDocumentId($docId);
}
