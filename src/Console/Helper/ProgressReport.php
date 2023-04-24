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

namespace Opus\Common\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function sprintf;

/**
 * Collects messages for steps in order to generate a report after processing is finished.
 */
class ProgressReport
{
    /** @var string */
    private $reportTitle = "<bg=red>There were %d documents with problems:</>";

    /** @var string */
    private $reportTitleSingular = "<bg=red>There was 1 document with problems:</>";

    /** @var ProgressReportEntry[] */
    private $entries = [];

    /** @var ProgressReportEntry */
    private $currentEntry;

    /**
     * @param string      $title
     * @param null|string $category
     */
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

    /**
     * @param Exception   $e
     * @param null|string $message
     */
    public function addException($e, $message = null)
    {
        if ($this->currentEntry === null) {
            $this->startEntry(null);
        }

        $this->currentEntry->addException($e);
    }

    /**
     * @param OutputInterface $output
     */
    public function write($output)
    {
        $entryCount = count($this->entries);

        if ($entryCount === 0) {
            return;
        } elseif ($entryCount === 1) {
            $reportTitleFormat = $this->reportTitleSingular;
        } else {
            $reportTitleFormat = $this->reportTitle;
        }

        $reportTitle = sprintf($reportTitleFormat, $entryCount);
        $output->writeln('');
        $output->writeln($reportTitle);
        $output->writeln('');

        foreach ($this->entries as $index => $entry) {
            $header = sprintf("%d) %s", $index + 1, $entry->getTitle());
            $output->writeln($header);
            $output->writeln('');

            foreach ($entry->getExceptions() as $e) {
                $output->writeln($e->getMessage());
            }

            $output->writeln('');
        }
    }

    /**
     * @param string      $title
     * @param null|string $category
     */
    public function setEntryInfo($title, $category = null)
    {
        if ($this->currentEntry !== null) {
            $this->currentEntry->setTitle($title);
            $this->currentEntry->setCategory($category);
        }
    }

    /**
     * @return ProgressReportEntry
     */
    public function getCurrentEntry()
    {
        return $this->currentEntry;
    }

    public function clear()
    {
        $this->entries      = [];
        $this->currentEntry = null;
    }
}
