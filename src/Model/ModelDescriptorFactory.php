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

/**
 * Manages model descriptor objects.
 *
 * This class is a singleton that holds the ModelDescriptor objects for all model classes. It is responsible for
 * creating the ModelDescriptor objects based on basic configurations provided for the common model classes and
 * additional local configurations for a specific OPUS 4 installation. The configuration of a model can also be
 * extended by other packages or a persistence implementation of the data model.
 *
 * TODO create ModelDescriptor objects with basic configuration
 * TODO support extensions by the Framework
 * TODO support custom fields/enrichments configured in Application
 * TODO support extensions by other packages
 *
 * Extensions can be
 *
 * - additional information stored in a model or field descriptor.
 * - custom fields, simple or complex
 */
class ModelDescriptorFactory
{
    /** @var array Map of model IDs and ModelDescriptor objects */
    private $descriptors;

    /**
     * Returns ModelDescriptor for model ID.
     *
     * @param string $modelId
     * @return ModelDescriptor|null
     *
     * TODO provide model object or class as parameter?
     * TODO use callback to get data from basic model class?
     */
    public function getModelDescriptor($modelId)
    {
        if (isset($this->descriptors[$modelId])) {
            return $this->descriptors[$modelId];
        }

        return null;
    }

    /**
     * @param string $modelId
     * @param array  $config
     * @return ModelDescriptor
     */
    public function loadModelDescriptor($modelId, $config)
    {
        $modelDescriptor             = new ModelDescriptor($modelId, $config);
        $this->descriptors[$modelId] = $modelDescriptor;
        return $modelDescriptor;
    }
}
