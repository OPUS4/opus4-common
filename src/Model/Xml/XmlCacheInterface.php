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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Model\Xml;

use DOMDocument;

/**
 * Interface for object caching XML representation of documents.
 *
 * The XML is used for generating output using XSLT, for instance by the
 * frontdoor implementation and exports. Using the cache, the performance
 * can be improved significantly.
 */
interface XmlCacheInterface
{
    /**
     * Returns DOMDocument for document if found in cache.
     *
     * @param int    $documentId
     * @param string $xmlVersion
     * @return null|DOMDocument
     */
    public function get($documentId, $xmlVersion);

    /**
     * Returns XML for document from cache.
     *
     * @param int|int[] $documentId
     * @param string    $xmlVersion
     * @return null|string|string[]
     */
    public function getData($documentId, $xmlVersion);

    /**
     * Returns entire cache as array.
     *
     * @return mixed
     */
    public function getAllEntries();

    /**
     * Check if cache has entry for document and format.
     *
     * @param int    $documentId
     * @param string $xmlVersion
     * @return mixed
     */
    public function hasCacheEntry($documentId, $xmlVersion);

    /**
     * Check if cache has current entry for document and format.
     *
     * @param int    $documentId
     * @param string $xmlVersion
     * @param mixed  $serverDateModified
     * @return bool
     */
    public function hasValidEntry($documentId, $xmlVersion, $serverDateModified);

    /**
     * Puts document XML into cache.
     *
     * @param int         $documentId
     * @param string      $xmlVersion
     * @param mixed       $serverDateModified
     * @param DOMDocument $xmlData
     * @return mixed
     */
    public function put($documentId, $xmlVersion, $serverDateModified, $xmlData);

    /**
     * Removes document entries from cache.
     *
     * @param int         $documentId
     * @param null|string $xmlVersion
     * @return mixed
     */
    public function remove($documentId, $xmlVersion = null);

    /**
     * Removes all entries from cache.
     */
    public function clear();

    /**
     * @param string $select
     *
     * TODO database dependent - move out of interface and implementation
     */
    public function removeAllEntriesWhereSubSelect($select);

    /**
     * @param mixed $model
     *
     * TODO database dependent - move out of interface and implementation
     */
    public function removeAllEntriesForDependentModel($model);
}
