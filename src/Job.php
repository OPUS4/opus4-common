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

use Opus\Common\Model\AbstractModel;

class Job extends AbstractModel
{
    public const FIELD_DATA   = 'Data';
    public const FIELD_LABEL  = 'Label';
    public const FIELD_STATE  = 'State';
    public const FIELD_ERRORS = 'Errors';

    public const STATE_PROCESSING = 'processing';
    public const STATE_FAILED     = 'failed';
    public const STATE_UNDEFINED  = 'undefined';

    /**
     * @param int[]|null $ids
     * @return JobInterface[]
     */
    public static function getAll($ids = null)
    {
        return self::getModelRepository()->getAll($ids);
    }

    /**
     * @param string|null $state
     * @return int
     */
    public static function getCount($state = null)
    {
        return (int) self::getModelRepository()->getCount($state);
    }

    /**
     * @param string      $label
     * @param string|null $state
     * @return int
     */
    public static function getCountForLabel($label, $state = null)
    {
        return (int) self::getModelRepository()->getCountForLabel($label, $state);
    }

    /**
     * @param string|null $state
     * @return array
     */
    public static function getCountPerLabel($state = null)
    {
        return self::getModelRepository()->getCountPerLabel($state);
    }

    /**
     * @param string[]    $labels
     * @param int|null    $limit
     * @param string|null $state
     * @return JobInterface[]
     */
    public static function getByLabels($labels, $limit = null, $state = null)
    {
        return self::getModelRepository()->getByLabels($labels, $limit, $state);
    }

    /**
     * Deletes all stored jobs.
     */
    public static function deleteAll()
    {
        self::getModelRepository()->deleteAll();
    }

    /**
     * @return array
     */
    protected static function loadModelConfig()
    {
        return [];
    }
}
