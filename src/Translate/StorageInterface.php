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
 * Interface StorageInterface for persisting custom translations.
 *
 * Custom translations used enrichments, collections and other information shown in OPUS 4 are currently stored in the
 * database. That made handling of custom translations easier. However in the future it might make sense to go back to
 * storing these customizations in TMX files in a central location. This would make it easier to modify customizations
 * with external tools. This interface is meant to decouple the actual storage method from the rest of OPUS 4.
 *
 * The concept of modules comes from the structure of OPUS 4 and Zend applications in general. The application is
 * divided into multiple module that each have their own translation resources. However in OPUS 4 users customize the
 * translations for most installations. So it isn't just a question of providing English, German and maybe support for
 * other languages, but it is necessary to support local customization that is easier than doing a fork to modify the
 * TMX files.
 *
 * For most operations the module of a translation is not important. All translations are always loaded, because one
 * module can depend on another and at runtime that dependency is not obvious for translation purposes. If a key is
 * used in multiple modules only one translation will be used, the one loaded last.
 *
 * TODO describe details of modules handling in API (expected behaviour)
 * TODO describe how to handle conflicts between modules
 * TODO cleanup interface
 */
interface StorageInterface
{
    /**
     * Removes a key from translation storage.
     *
     * If a module is specified only the entry for that module is removed. Otherwise all matching keys are removed.
     *
     * @param string      $key
     * @param null|string $module
     */
    public function remove($key, $module = null);

    /**
     * Removes all custom translations.
     */
    public function removeAll();

    /**
     * Removes all translations for a module.
     *
     * @param string $module
     */
    public function removeModule($module);

    /**
     * Sets the translation of a key for all languages.
     *
     * @param string $key
     * @param array  $translation
     * @param string $module
     */
    public function setTranslation($key, $translation, $module = 'default');

    /**
     * Returns translation value for a key.
     *
     * @param string      $key
     * @param null|string $locale
     * @param null|string $module
     * @return array
     */
    public function getTranslation($key, $locale = null, $module = null);

    /**
     * Finds all matching translations.
     *
     * @param string $needle
     * @return array
     */
    public function findTranslation($needle);

    /**
     * Returns all translations for all languages.
     *
     * @param null|string $module
     * @return array
     */
    public function getTranslations($module = null);

    /**
     * Returns all translations for a language.
     *
     * This function is used by the OPUS 4 implementation of the Zend_Translate_Adapter in order to provide the
     * translations stored in the database to the Zend translation mechanism. This is the only function necessary for
     * that. All the other functions are for the management user interface.
     *
     * @param null|string $module
     * @return array
     */
    public function getTranslationsByLocale($module = null);

    /**
     * @param array  $translations
     * @param string $module
     * @return mixed
     */
    public function addTranslations($translations, $module = 'default');

    /**
     * Returns all translations.
     *
     * @return array
     */
    public function getAll();

    /**
     * @param string $key
     * @param string $newKey
     * @param string $module
     */
    public function renameKey($key, $newKey, $module = 'default');

    /**
     * @param null|string[] $modules
     * @return mixed
     *
     * TODO replace getTranslations with this (interface should always provide module information)
     */
    public function getTranslationsWithModules($modules = null);

    /**
     * Returns the names of the modules with custom translations.
     *
     * @return string[]
     */
    public function getModules();
}
