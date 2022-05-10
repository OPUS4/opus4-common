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

use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Adapter for Symfoy class ProgressBar in order to use it interchangeably with other
 * OPUS 4 ProgressOutput classes.
 */
class ProgressBar extends AbstractBaseProgressOutput
{
    /** @var SymfonyProgressBar */
    private $progressBar;

    /**
     * @param int $max
     */
    public function __construct(OutputInterface $output, $max)
    {
        parent::__construct($output, $max);

        $this->progressBar = new SymfonyProgressBar($output, $max);
        $this->progressBar->setBarWidth(69 - 2 * $this->maxDigits); // TODO use get functions
    }

    public function start()
    {
        parent::start();
        $this->progressBar->start();
    }

    public function finish()
    {
        $this->progressBar->finish();
        parent::finish();
        $this->output->writeln('');
    }

    /**
     * @param int        $step
     * @param null|mixed $status
     */
    public function advance($step = 1, $status = null)
    {
        $this->progressBar->advance($step);
    }

    /**
     * @param int        $step
     * @param null|mixed $status
     */
    public function setProgress($step, $status = null)
    {
        $this->progressBar->setProgress($step);
    }
}
