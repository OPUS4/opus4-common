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

use Opus\Common\Model\ModelInterface;

interface CollectionInterface extends ModelInterface
{
    /**
     * Move all documents to another collection.
     *
     * All documents are removed from source collection, even if they are already present in the destination
     * collection. Last modified is updated for all source collections, if update is enabled.
     *
     * @param int  $destColId
     * @param bool $updateLastModified
     */
    public function moveDocuments($destColId, $updateLastModified = true);

    /**
     * Copy all documents to another collection.
     *
     * Only documents that are not already present in destination collection are copied. Last modified is updated
     * only for documents that were copied, if update is enabled.
     *
     * @param int  $destColId
     * @param bool $updateLastModified
     */
    public function copyDocuments($destColId, $updateLastModified = true);

    /**
     * Remove documents from collection.
     *
     * @param int[]|null $docIds
     * @param bool       $updateLastModified
     */
    public function removeDocuments($docIds = null, $updateLastModified = true);
}
