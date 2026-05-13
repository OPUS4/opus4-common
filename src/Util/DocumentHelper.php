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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Util;

use Opus\Common\ConfigTrait;

use function array_key_exists;
use function ctype_digit;
use function is_int;
use function preg_split;
use function strval;
use function trim;

use const PREG_SPLIT_NO_EMPTY;

/**
 * Determines the year for a document depending on the configuration.
 */
class DocumentHelper
{
    use ConfigTrait;

    /** @var string */
    private $yearFieldOrder;

    public function getYear(string $publishedDateYear, string $publishedYear, string $completedDateYear, string $completedYear): int
    {
        $fields                  = [];
        $fields['PublishedDate'] = $publishedDateYear;
        $fields['PublishedYear'] = $publishedYear;
        $fields['CompletedDate'] = $completedDateYear;
        $fields['CompletedYear'] = $completedYear;

        $year = '';

        $order = $this->getYearFieldOrder();

        foreach ($order as $fieldName) {
            if (array_key_exists($fieldName, $fields)) {
                $year = $fields[$fieldName];
                if (is_int($year)) {
                    $year = strval($year);
                }
                if ($year !== null && ctype_digit($year)) {
                    // use the first value found
                    break;
                }
            }
        }

        return $year;
    }

    public function getYearFieldOrder(): array
    {
        if ($this->yearFieldOrder === null) {
            $config = $this->getConfig();

            if (isset($config->search->index->field->year->order)) {
                $orderConfig = $config->search->index->field->year->order;
            } else {
                $orderConfig = 'PublishedDate,PublishedYear'; // old default
            }

            $order = preg_split('/[\s,]+/', trim($orderConfig), 0, PREG_SPLIT_NO_EMPTY);

            $this->yearFieldOrder = $order;
        }

        return $this->yearFieldOrder;
    }
}
