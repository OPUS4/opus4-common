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

/**
 * Class ProgressOutput
 * @package Opus\Search\Console
 *
 * TODO modify interface to match ProgressBar more closely
 * TODO separate setProgress from displaying
 * TODO handle creating "blocks" here, so Command can call advance for every document (while output gets only
 *      updated every 10)
 */
class ProgressOutput extends BaseProgressOutput
{

    /**
     * @var string
     * TODO make format configurable
     */
    protected $format;

    /**
     * Output current processing status and performance.
     *
     * @param $runtime long Time of start of processing
     * @param $numOfDocs Number of processed documents
     *
     * TODO handle startTime = null, because start() was forgotten
     */
    public function setProgress($progress)
    {
        parent::setProgress($progress);

        $memNow = round(memory_get_usage() / 1024 / 1024);
        $memPeak = round(memory_get_peak_usage() / 1024 / 1024);

        $currentTime = microtime(true);

        $deltaTime = $currentTime - $this->startTime;
        $docPerSecond = round($deltaTime) == 0 ? 'inf' : round($progress / $deltaTime, 2);
        $secondsPerDoc = round($deltaTime / $progress, 2);

        $message = sprintf(
            "%s Stats after <fg=yellow>%{$this->maxDigits}d</> docs -- mem <fg=yellow>%3d</> MB, peak <fg=yellow>%3d</> MB, <fg=yellow>%6.2f</> docs/s, <fg=yellow>%5.2f</> s/doc",
            date('Y-m-d H:i:s'),
            $progress,
            $memNow,
            $memPeak,
            $docPerSecond,
            $secondsPerDoc
        );

        $this->output->writeln($message);
    }

    /**
     * TODO separate output function from setProgress
     */
    protected function display()
    {
    }

    public function advance($steps = 1)
    {
        // TODO: Implement advance() method.
    }
}
