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
 * Displays progress like PHPUnit with different characters to signal status for each step.
 *
 * Uses following characters:
 * - '.' for every successful step (or unknown status)
 * - 'F' for failures
 * - 'w' for warnings
 *
 * The line length of the output should be limited to 80 characters.
 *
 * .......................................................II......  63 / 187 ( 33%)
 *
 * TODO second step different status
 * TODO make status options configurable with defaults
 * TODO class using ProgressMatrix should not have to worry about formatting - just provide result for step(s)
 * TODO support providing status in array for block of steps
 *
 * @package Opus\Console\Helper
 */
class ProgressMatrix extends BaseProgressOutput
{

    private $maxLineLength;

    private $currentLineLength;

    public function __construct($output, $max)
    {
        parent::__construct($output, $max);

        $this->maxLineLength = 80 - 2 * $this->maxDigits - 12;
    }

    public function start()
    {
        parent::start();
        $this->currentLineLength = 0;
    }

    /**
     * TODO output Time, Memory and ?
     */
    public function finish()
    {
        parent::finish();
        $this->output->writeln('');
    }

    /**
     * @param $step
     * @param null $status
     *
     * TODO handle going backwards?
     * TODO handle step > max
     */
    public function setProgress($step, $status = null)
    {
        for ($i = $this->progress; $i < $step; $i++) {
            $this->progress++;
            $this->currentLineLength++;
            if ($status === null) {
                $this->output->write('.');
            } else {
                $this->output->write($status);
            }

            if ($this->currentLineLength > $this->maxLineLength) {
                $percent = $this->progress * 100.0 / $this->max;
                $message = sprintf("  %{$this->maxDigits}d / %{$this->maxDigits}d (%3d%%)", $this->progress, $this->max, $percent);
                $this->output->writeln($message);
                $this->currentLineLength = 0;
            }
        }
    }

    protected function display()
    {
    }
}
