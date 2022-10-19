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

namespace Opus\Common\Model;

use function ucfirst;

/**
 * TODO This class is used to keep Opus\Common\Date compatible to the old Framework implementation and the code for
 *      generating XML. The old Field class still exists and is used by the entity classes in the Framework. For Date
 *      the Field objects are only created on demand and not at construction of the object.
 *
 * TODO LAMINAS keep this?
 */
class Field implements FieldInterface
{
    private $model;

    private $name;

    private $valueModelClass;

    /**
     * @param ModelInterface $model
     * @param string         $name
     * @param string|null    $valueModelClass
     */
    public function __construct($model, $name, $valueModelClass = null)
    {
        $this->model           = $model;
        $this->name            = $name;
        $this->valueModelClass = $valueModelClass;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getValueModelClass()
    {
        return $this->valueModelClass;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $getter = 'get' . ucfirst($this->name);

        return $this->model->$getter();
    }

    /**
     * @return false
     */
    public function hasMultipleValues()
    {
        return false; // TODO LAMINAS get from model description
    }
}
