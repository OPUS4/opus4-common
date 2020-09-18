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
 * @category    Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Collects messages for steps in order to generate a report after processing is finished.
 *
 * @package Opus\Console\Helper
 *
 * TODO maybe this can only a base class that needs to be extended for the concrete purpose like ExtractionReport
 * TODO need to be able to add multiple messages for each step (separately, like for each file of a document)
 * TODO need to support categories (Failure, Error) - see PHPUnit
 * TODO find a better name
 * TODO messages added without entry have global scope
 * TODO customize report output for commands (child classes?)
 */
class ProgressReport
{

    private $reportTitle = "<bg=red>There were %d documents with problems:</>";

    /**
     * @var ProgressReportEntry[]
     */
    private $entries = [];

    /**
     * @var ProgressReportEntry
     */
    private $currentEntry;

    public function startEntry($title, $category = null)
    {
        $this->currentEntry = new ProgressReportEntry();
        $this->setEntryInfo($title, $category);
        $this->entries[] = $this->currentEntry;
    }

    public function finishEntry()
    {
        $this->currentEntry = null;
    }

    public function addException($e, $message = null)
    {
        if ($this->currentEntry === null) {
            $this->startEntry(null);
        }

        $this->currentEntry->addException($e);
    }

    public function write($output)
    {
        $entryCount = count($this->entries);

        $maxDigits = strlen(( string )$entryCount);

        $reportTitle = sprintf($this->reportTitle, $entryCount);
        $output->writeln('');
        $output->writeln($reportTitle);
        $output->writeln('');

        foreach ($this->entries as $index => $entry) {
            $header = sprintf("%{$maxDigits}d) %s", $index + 1, $entry->getTitle());
            $output->writeln($header);
            $output->writeln('');

            foreach ($entry->getExceptions() as $e) {
                $output->writeln($e->getMessage());
            }

            $output->writeln('');
        }
    }

    public function setEntryInfo($title, $category = null)
    {
        if ($this->currentEntry !== null) {
            $this->currentEntry->setTitle($title);
            $this->currentEntry->setCategory($category);
        }
    }

    public function getCurrentEntry()
    {
        $this->currentEntry;
    }

    public function clear()
    {
    }
}
