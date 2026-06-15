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

interface LicenceInterface extends ModelInterface
{
    /**
     * @return bool
     */
    public function getActive();

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive($active);

    /**
     * @return string|null
     */
    public function getCommentInternal();

    /**
     * @param string|null $comment
     * @return $this
     */
    public function setCommentInternal($comment);

    /**
     * @return string|null
     */
    public function getDescMarkup();

    /**
     * @param string|null $descriptionMarkup
     * @return $this
     */
    public function setDescMarkup($descriptionMarkup);

    /**
     * @return string|null
     */
    public function getDescText();

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescText($description);

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
    public function getLinkLicence();

    /**
     * @param string|null $link
     * @return $this
     */
    public function setLinkLicence($link);

    /**
     * @return string|null
     */
    public function getLinkLogo();

    /**
     * @param string|null $link
     * @return $this
     */
    public function setLinkLogo($link);

    /**
     * @return string|null
     */
    public function getLinkSign();

    /**
     * @param string|null $link
     * @return $this
     */
    public function setLinkSign($link);

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
    public function getName();

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getNameLong();

    /**
     * @param string $nameLong
     * @return $this
     */
    public function setNameLong($nameLong);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $position
     * @return $this
     */
    public function setSortOrder($position);

    /**
     * @return bool
     */
    public function getPodAllowed();

    /**
     * @param bool $allowed
     * @return $this
     */
    public function setPodAllowed($allowed);

    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @return bool
     */
    public function isUsed();

    /**
     * @return int
     */
    public function getDocumentCount();
}
