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

namespace Opus\Common\Security;

interface RealmStorageInterface
{
    /**
     * Get the roles that are assigned to the specified username.
     *
     * @param string $username username to be set.
     * @throws SecurityException Thrown if the supplied identity could not be found.
     * @return array Array of assigned roles or an empty array.
     */
    public function getUsernameRoles($username);

    /**
     * Map an IP address to Roles.
     *
     * @param string $ipaddress ip address to be set.
     * @throws SecurityException Thrown if the supplied ip is not valid.
     * @return array Array of assigned roles or an empty array.
     */
    public function getIpaddressRoles($ipaddress);

    /**
     * Returns all module resources to which the current user and ip address
     * has access.
     *
     * @param string|null $username  name of the account to get resources for.
     *                               Defaults to currently logged in user
     * @param string|null $ipaddress IP address to get resources for.
     *                               Defaults to current remote address if available.
     * @return array Module resource names
     * @throws SecurityException Thrown if the supplied ip is not valid or user can not be determined.
     */
    public function getAllowedModuleResources($username = null, $ipaddress = null);

    /**
     * Checks, if the logged user is allowed to access (document_id).
     *
     * @param string   $documentId ID of the document to check
     * @param string[] $roles
     * @return bool Returns true only if access is granted.
     */
    public function checkDocument($documentId, $roles);

    /**
     * Checks, if the logged user is allowed to access (file_id).
     *
     * @param string   $fileId ID of the file to check
     * @param string[] $roles
     * @return bool Returns true only if access is granted.
     */
    public function checkFile($fileId, $roles);

    /**
     * Checks, if the logged user is allowed to access (module_name).
     *
     * @param string   $moduleName Name of the module to check
     * @param string[] $roles
     * @return bool Returns true only if access is granted.
     */
    public function checkModule($moduleName, $roles);

    /**
     * Checks if a user has access to a module.
     *
     * @param string $moduleName Name of module
     * @param string $user Name of user
     * @return bool
     */
    public function checkModuleForUser($moduleName, $user);
}
