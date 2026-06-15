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

/**
 * DnbInstitute for specifying granting and publishing organisations.
 *
 * The interface is fluent. The set-Functions should return a reference to the object, so calls can be chained.
 */
interface DnbInstituteInterface extends ModelInterface
{
    /**
     * Name is a required field.
     *
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getDepartment();

    /**
     * @param string|null $department
     * @return $this
     */
    public function setDepartment($department);

    /**
     * @return string|null
     */
    public function getAddress();

    /**
     * @param string|null $address
     * @return $this
     */
    public function setAddress($address);

    /**
     * City is a required field.
     *
     * @return string|null
     */
    public function getCity();

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * @return string|null
     */
    public function getPhone();

    /**
     * @param string|null $phone
     * @return $this
     */
    public function setPhone($phone);

    /**
     * @return string|null
     */
    public function getDnbContactId();

    /**
     * @param string|null $dnbContactId
     * @return $this
     */
    public function setDnbContactId($dnbContactId);

    /**
     * @return bool
     */
    public function getIsGrantor();

    /**
     * @param bool $isGrantor
     * @return $this
     */
    public function setIsGrantor($isGrantor);

    /**
     * @return bool
     */
    public function getIsPublisher();

    /**
     * @param bool $isPublisher
     * @return $this
     */
    public function setIsPublisher($isPublisher);

    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @return bool Returns TRUE if institute has been used for any document as publisher or grantor
     */
    public function isUsed();
}
