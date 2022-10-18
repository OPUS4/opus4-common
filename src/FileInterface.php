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

interface FileInterface extends ModelInterface
{
    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string|null $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * @return int
     */
    public function getFileSize();

    /**
     * @param int $size
     * @return $this
     */
    public function setFileSize($size);

    /**
     * @return string|null
     */
    public function getHashValue();

    /**
     * @param string|null $hash
     * @return $this
     */
    public function setHashValue($hash);

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string|null
     */
    public function getLanguage();

    /**
     * @param string|null $lang
     * @return $this
     */
    public function setLanguage($lang);

    /**
     * @return string|null
     */
    public function getMimeType();

    /**
     * @param string|null $mimeType
     * @return $this
     */
    public function setMimeType($mimeType);

    /**
     * @return string|null
     */
    public function getPathName();

    /**
     * @param string $pathName
     * @return $this
     */
    public function setPathName($pathName);

    /**
     * @return Date|null
     */
    public function getServerDateSubmitted();

    /**
     * @param Date|null $dateSubmitted
     * @return $this
     */
    public function setServerDateSubmitted($dateSubmitted);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $pos
     * @return $this
     */
    public function setSortOrder($pos);

    /**
     * @return bool
     */
    public function getVisibleInFrontdoor();

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisibleInFrontdoor($visible);

    /**
     * @return bool
     */
    public function getVisibleInOai();

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisibleInOai($visible);
}
