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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common;

use function array_diff;
use function array_map;
use function explode;
use function is_string;

/**
 * TODO this should probably inherit from the "FieldDescriptor" class or something similar
 *      Because of that the functions should not be static. The code using this should obtain a descriptor class for
 *      field PublicationState that provides this functionality.
 */
class PublicationState implements PublicationStateConstantsInterface
{
    use ConfigTrait;

    /** @var string[] Supported database values for PublicationState */
    private $values = [
        'draft',
        'authorsVersion',
        'submittedVersion',
        'acceptedVersion',
        'proof',
        'publishedVersion',
        'correctedVersion',
        'enhancedVersion',
    ];

    /**
     * Returns all possible values for PublicationState.
     *
     * This mirrors the ENUM definition in the database schema.
     *
     * TODO read from the database metadata?
     *
     * @return string[]
     */
    public function getAllValues()
    {
        return $this->values;
    }

    /**
     * Returns all allowed values for PublicationState.
     *
     * model.document.fields.PublicationState.excludeValues =
     *
     * @return string[]
     */
    public function getValues()
    {
        $config = $this->getConfig();

        if (isset($config->model->document->fields->PublicationState->excludeValues)) {
            $excludeValues = $config->model->document->fields->PublicationState->excludeValues;
            if (is_string($excludeValues)) {
                $excludeValues = explode(',', $excludeValues);
            } else {
                $excludeValues = $excludeValues->toArray();
            }

            $excludeValues = array_map('trim', $excludeValues);
        } else {
            $excludeValues = [];
        }

        return array_diff($this->values, $excludeValues);
    }
}
