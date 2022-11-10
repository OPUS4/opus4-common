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

use Opus\Common\Config;
use Opus\Common\Config\ConfigException;

use function array_merge;
use function array_unique;
use function filter_var;
use function in_array;
use function preg_match;

use const FILTER_VALIDATE_BOOLEAN;

/**
 * TODO find better name for "RealmStorage"
 */
class Realm implements RealmInterface
{
    /** @var RealmStorageInterface */
    private $realmStorage;

    /* Singleton code below                                                                     */

    /**
     * Holds instance.
     *
     * @var RealmInterface
     */
    private static $instance;

    /**
     * Delivers the singleton instance.
     *
     * @return RealmInterface
     *
     * TODO make Realm implementation configurable so a child class of Realm could be used
     */
    final public static function getInstance()
    {
        if (null === self::$instance) {
            $class          = static::class;
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * Disallow construction.
     */
    final private function __construct()
    {
    }

    /**
     * Singleton classes cannot be cloned!
     */
    final private function __clone()
    {
    }

    /**
     * Checks, if the logged user is allowed to access (document_id).
     *
     * @param null|string $documentId ID of the document to check
     * @return bool Returns true only if access is granted.
     */
    public function checkDocument($documentId = null)
    {
        if ($this->skipSecurityChecks()) {
            return true;
        }

        if (empty($documentId)) {
            return false;
        }

        return $this->getRealmStorage()->checkDocument($documentId, $this->roles);
    }

    /**
     * Checks, if the logged user is allowed to access (file_id).
     *
     * @param null|string $fileId ID of the file to check
     * @return bool Returns true only if access is granted.
     */
    public function checkFile($fileId = null)
    {
        if ($this->skipSecurityChecks()) {
            return true;
        }

        if (empty($fileId)) {
            return false;
        }

        return $this->getRealmStorage()->checkFile($fileId, $this->roles);
    }

    /**
     * Checks, if the logged user is allowed to access (module_name).
     *
     * @param null|string $moduleName Name of the module to check
     * @return bool Returns true only if access is granted.
     */
    public function checkModule($moduleName = null)
    {
        if ($this->skipSecurityChecks()) {
            return true;
        }

        if (empty($moduleName)) {
            return false;
        }

        return $this->getRealmStorage()->checkModule($moduleName, $this->roles);
    }

    /**
     * Checks if a user has access to a module.
     *
     * @param string $moduleName Name of module
     * @param string $user Name of user
     * @return bool
     */
    public static function checkModuleForUser($moduleName, $user)
    {
        return self::getInstance()->getRealmStorage()->checkModuleForUser($moduleName, $user);
    }

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
    public static function getAllowedModuleResources($username = null, $ipaddress = null)
    {
        if ($ipaddress !== null && ! self::validateIpAddress($ipaddress)) {
            throw new SecurityException('Your IP address could not be validated.');
        }

        if (empty($ipaddress) && empty($username)) {
            throw new SecurityException('username and / or IP address must be provided.');
        }

        return self::getInstance()->getRealmStorage()->getAllowedModuleResources($username, $ipaddress);
    }

    /**
     * @return RealmStorageInterface
     * @throws ConfigException
     */
    protected function getRealmStorage()
    {
        if ($this->realmStorage === null) {
            $storageClass       = self::getRealmStorageClass();
            $this->realmStorage = new $storageClass();
        }

        return $this->realmStorage;
    }

    /**
     * @return string
     * @throws ConfigException
     */
    protected static function getRealmStorageClass()
    {
        $config = Config::get();

        if (isset($config->securityStorageClass)) {
            return $config->securityStorageClass;
        } else {
            throw new ConfigException('Security.realmStorageClass not configured');
        }
    }

    /**
     * The current user roles (merged userRoles and ipaddressRoles).
     *
     * @var array
     */
    protected $roles = ['guest'];

    /**
     * The current user roles (based on the user name).
     *
     * @var array
     */
    protected $userRoles = [];

    /**
     * Thre current ip address
     *
     * @var string
     */
    protected $ipaddressRoles = [];

    /** @var string Client IP address */
    private $clientIp;

    /**
     * Set the current username.
     *
     * @param string $username username to be set.
     * @throws SecurityException Thrown if the supplied identity could not be found.
     * @return $this Fluent interface.
     */
    public function setUser($username)
    {
        // reset "old" credentials
        $this->userRoles = [];
        $this->setRoles();

        $this->userRoles = $this->getRealmStorage()->getUsernameRoles($username);
        $this->setRoles();
        return $this;
    }

    /**
     * Set the current ip address.
     *
     * @param string $ipaddress ip address to be set.
     * @throws SecurityException Thrown if the supplied ip address is not a valid ip address.
     * @return $this Fluent interface.
     */
    public function setIp($ipaddress)
    {
        $this->clientIp = null;

        // reset "old" credentials
        $this->ipaddressRoles = [];
        $this->setRoles();

        $this->ipaddressRoles = $this->getRealmStorage()->getIpaddressRoles($ipaddress);
        $this->setRoles();

        $this->clientIp = $ipaddress;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIp()
    {
        return $this->clientIp;
    }

    /**
     * Set internal roles from current username/ipaddress.
     * Adds the default role "guest", if not done by username/ipaddress.
     *
     * @return $this Fluent interface.
     */
    private function setRoles()
    {
        $this->roles   = array_merge($this->userRoles, $this->ipaddressRoles);
        $this->roles[] = 'guest';

        $this->roles = array_unique($this->roles);
        return $this;
    }

    /**
     * checks if the string provided is a valid ip address
     *
     * @param string $ipaddress ip address to validate.
     * @return bool Returns true if validation succeeded
     */
    public static function validateIpAddress($ipaddress)
    {
        $regex = '/^(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.'
            . '(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.'
            . '(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.'
            . '(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/';
        return preg_match($regex, $ipaddress) === 1;
    }

    /**
     * Check if user with administrator-role or security is disabled.
     *
     * @return bool
     */
    public function skipSecurityChecks()
    {
        // Check if security is switched off
        $conf = Config::get();
        if (isset($conf->security) && (! filter_var($conf->security, FILTER_VALIDATE_BOOLEAN))) {
            return true;
        }

        if (true === in_array('administrator', $this->roles)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the names of the roles for current user and ip address range.
     *
     * @return array of strings - Names of roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Checks if a privilege is granted for actual context (usersession, ip address).
     * If administrator is one of the current roles true will be returned ingoring everything else.
     *
     * @deprecated
     *
     * @param string      $privilege
     * @param string|null $documentServerState
     * @param int|null    $fileId
     * @return bool
     */
    public function check($privilege, $documentServerState = null, $fileId = null)
    {
        return $this->skipSecurityChecks();
    }
}
