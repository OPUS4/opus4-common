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

namespace Opus\Common;

/**
 * Interface for finding IDs of documents.
 *
 * The interface is similar to query builder interfaces in Zend or Doctrine. However our implementation might use the
 * database or Solr or some other method for finding matching documents. The goal here is to hide that from the code
 * in the OPUS 4 application or custom scripts.
 *
 * The interface is fluent, so all set functions return $this.
 *
 * If no conditions are set, the finder matches all documents.
 *
 * TODO setFunktionen in whereXXXX umbenennen?
 *
 * Überlegungen zum Design (im Vergleich mit der alten Klasse DocumentFinder)
 *
 * Alle Konditionen werden mit Und-verknüpft. Um alle freigeschalteten Artikel zu finden, kann man also wie folgt zwei
 * Bedingungen setzen.
 *
 *     $finder->setServerState('published')->setDocumentType('article')->getId();
 *
 * Um die Dokument für zwei Werte von ServerState zu finden, kann man in der alten Implementation mit Zend_Db nicht
 * einfach zweimal die Funktion setServerState aufrufen, sondern muss setServerStateInList verwenden. Wenn zweimal
 * setServerState aufrufen wird gäbe es keinen Fehler, sondern beide Bedingungen würden hinzugefügt. Da kein Document
 * gleichzeitig beide Bedingungen erfüllen kann wäre das Ergebnis leer.
 *
 * Funktionen wie setIdentifierValue($name, $value) können mehrfach aufgerufen werden und fügen weitere Bedingungen zur
 * Abfrage. Wird der gleiche Identifier-Name zweimal verwendet werden die Werte nicht mit ODER verknüpft.
 *
 * TODO Wie soll sich die neue API verhalten?
 *
 * Das Query-Object, z.B. der DBAL QueryBuilder, das von der DocumentFinder Implementation verwendet wird, könnte mit
 * jedem Aufruf einer Set-Funktion erweitert werden. Dann werden allerdings alle Bedingungen mit UND verknüpft bzw. es
 * müssen spezielle Funktionen existieren, die ODER-Verknüpfungen für mehrere Werte eines Feldes erlauben.
 *
 * Alternativ könnten die Set-Funktionen die Bedingungen lediglich speichern und das Query-Objekt wird erst bei Aufruf
 * der Fetch-Funktionen (getIds oder getCount) konstruiert. Da dann alle Bedingungen vollständig sind, könnten UND und
 * ODER passend an den entsprechenden Stellen eingesetzt werden. Im Prinzip müssten die Bedingen in einer Array
 * Struktur gespeichert werden über die für die Konstruktion iteriert werden kann. Das hätte den Vorteil, dass man
 * Abfragen auch als Array bzw. JSON konfigurieren könnte.
 *
 * TODO Design? Configurable like old DocumentFinder with multiple set-Functions or find-Functions
 *      that have parameters and cover all use cases (state or stateless)
 *
 * TODO one class or find plugins?
 *
 *
 * TODO method of affecting sorting order used by action helper Documents
 *
 * TODO groupedServerYearPublished used by SitelinksController
 *
 * TODO limit by multiple states and types
 *
 * TODO get grouped by type and count for OAI sets
 *
 * TODO We might need a generic DocumentFinder for the common cases and special classes for some
 *      very specific functionality like finding documents that are out of date in cache. What
 *      is the plan for the future? We cannot have the DocumentFinder as a simple wrapper around SQL.
 *
 * TODO !!! Überlappungen mit DocumenRepository in Doctrine - the implementation of the interface could
 *      delegate to DocumentRepository
 */
interface DocumentFinderInterface
{
    const ORDER_ID                    = 'Id';
    const ORDER_AUTHOR                = 'Author';
    const ORDER_TITLE                 = 'Title';
    const ORDER_DOCUMENT_TYPE         = 'Type';
    const ORDER_SERVER_DATE_PUBLISHED = 'ServerDatePublished';

    /**
     * Returns identifier for matching documents.
     *
     * @return int[] Identifier for documents
     *
     * TODO rename to fetch?
     * TODO rename to getDocumentId?
     */
    public function getIds();

    /**
     * Returns count of matching documents.
     *
     * @return int Number of matching documents
     */
    public function getCount();

    /**
     * @param string $criteria Field for sorting
     * @param string $ascending Ascending direction for sorting, FALSE = descending
     * @return mixed
     *
     * TODO use constants for criteria and direction
     */
    public function setOrder($criteria, $ascending = true);

    /**
     * Sets subset of document IDs for matching.
     *
     * @param int[] $documentIds
     * @return mixed
     */
    public function setDocumentIds($documentIds);

    /**
     * Set range of document IDs as condition.
     *
     * @param int|null $start
     * @param int|null $end
     * @return mixed
     */
    public function setDocumentIdRange($start = null, $end = null);

    /**
     * Sets ServerState condition for query.
     *
     * When multiple values for ServerState are provided, OR is used.
     *
     * @param string|string[] $serverState ServerState(s) of documents
     * @return $this
     */
    public function setServerState($serverState);

    /**
     * @param bool $partOfBibliography
     * @return mixed
     */
    public function setBelongsToBibliography($partOfBibliography = true);

    /**
     * @param int|int[] $collectionId Identifier for collection
     * @return mixed
     */
    public function setCollectionId($collectionId);

    /**
     * Set link to collection in collection role as condition.
     *
     * The function can be called multiple times. A document then needs to be associated with all CollectionRoles. So
     * the operator is AND.
     *
     * @param int $roleId Identifier for CollectionRole
     * @return $this
     */
    public function setCollectionRoleId($roleId);

    /**
     * Set existence of type of identifier as condition.
     *
     * The function can be called multiple times. A document has to have all the set identifiers to match.
     *
     * @param string $name Type of identifier
     * @return $this
     */
    public function setIdentifierExists($name);

    /**
     * Set value of identifier as condition.
     *
     * @param string $name Name of identifier field
     * @param string $value Value of identifier
     * @return $this
     */
    public function setIdentifierValue($name, $value);

    /**
     * Set type of document as condition.
     *
     * @param string|string[] $type Type of document
     * @return $this
     */
    public function setDocumentType($type);

    /**
     * Set name of existing enrichment key as condition.
     *
     * @param string $name
     * @return mixed
     */
    public function setEnrichmentExists($name);

    /**
     * Set value of enrichment key as condition.
     *
     * @param string $key Name of enrichment
     * @param string $value Value of enrichment entry
     * @return mixed
     */
    public function setEnrichmentValue($key, $value);

    /**
     * @param string $date
     * @return mixed
     *
     * TODO merge functions for ServerDatePublished?
     * TODO Document usage of parameters
     */
    public function setServerDatePublishedBefore($date);

    /**
     * @param string $from
     * @param string $until
     * @return mixed
     */
    public function setServerDatePublishedRange($from, $until);

    /**
     * @param string $date
     * @return mixed
     */
    public function setServerDateModifiedBefore($date);

    /**
     * @param string $date
     * @return mixed
     */
    public function setServerDateModifiedAfter($date);

    /**
     * @param string $date
     * @return mixed
     */
    public function setEmbargoDateBefore($date);

    /**
     * @param string $date
     * @return mixed
     */
    public function setNotEmbargoedOn($date);

    /**
     * Adds condition where ServerDateModified is earlier than EmbargoDate.
     *
     * This condition is used to find documents that have not been updated after the expiration of their EmbargoDate.
     * An update of ServerDateModified is necessary to trigger harvesting in OAI for instance by DNB.
     *
     * @return mixed
     */
    public function setNotModifiedAfterEmbargoDate();

    /**
     * Add condition that requires documents to have files visible in OAI.
     *
     * @return mixed
     */
    public function setHasFilesVisibleInOai();

    /**
     * Add condition for document IDs not present in XML cache.
     *
     * @return mixed
     *
     * TODO Cache specific code, move to some Cache helper class? Cache might not be implemented in database?
     */
    public function setNotInXmlCache();

    /**
     * Returns types of found documents.
     *
     * @param bool $includeCount TRUE Additionally Returns document count for each type
     * @return array
     */
    public function getDocumentTypes($includeCount = false);

    /**
     * Returns publication years of found documents.
     *
     * This function returns an array of years found in ServerDatePublished of the matching documents.
     *
     * @return array
     */
    public function getYearsPublished();

    /**
     * @param string|string[] $state
     * @return mixed
     */
    public function setPublicationState($state);
}
