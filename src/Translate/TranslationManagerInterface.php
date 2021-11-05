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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Translate;

/**
 * Interface for modifying translations in OPUS 4.
 *
 * Translation keys have to be unique across modules, since all translations are loaded at runtime. If a key appears
 * in more than one module only one translation will be used, most likely the one loaded last.
 *
 * Sources of translations, like TMX files or the database, might contain duplicate keys. This needs to be handled
 * at the time of loading by logging a warning.
 *
 * TODO signal duplicate keys to administrators
 *
 * TODO convert interface for a stateless class (currently TranslationManager is not stateless)
 * TODO do we need some kind of Query and maybe a Result object?
 */
interface TranslationManagerInterface
{
    /**
     * @param string $sort
     * @param string $sortDirection
     * @return array
     *
     * TODO define default values
     */
    public function getTranslations($sort, $sortDirection);

    /**
     * @param string $key
     * @return array
     */
    public function getTranslation($key);

    /**
     * @param string $key
     * @param string $newKey
     * @param string $module // TODO do we need this parameter?
     */
    public function renameKey($key, $newKey, $module = 'default');

    /**
     * @param string      $key
     * @param array       $translations
     * @param null|string $module
     * @param null|string $oldKey
     */
    public function updateTranslation($key, $translations, $module = null, $oldKey = null);

    /**
     * @param string      $key
     * @param array       $values
     * @param null|string $module
     */
    public function setTranslation($key, $values, $module = null);

    /**
     * @param string      $key
     * @param null|string $module
     */
    public function delete($key, $module = null);

    public function deleteAll();

    public function deleteMatches();

    /**
     * @param string $key
     * @return bool
     */
    public function keyExists($key);
}
