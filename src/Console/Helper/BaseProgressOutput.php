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
 * @package     Application_Console
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for ProgressOutput helper.
 *
 * @package Opus\Console\Helper
 *
 * TODO do we need to get time as float?
 */
abstract class BaseProgressOutput implements ProgressOutputInterface
{

    /**
     * @var int Maximum number of steps (progress)
     */
    protected $max;

    /**
     * @var int Number of digits to display maximum number of steps
     */
    protected $maxDigits;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var float Time progress started
     */
    protected $startTime;

    /**
     * @var float Time progress ended
     */
    protected $endTime;

    public function __construct(OutputInterface $output, $max = 0)
    {
        $this->output = $output;
        $this->max = $max;
        $this->maxDigits = strlen(( string )$max);
    }

    /**
     * Starts progress.
     */
    public function start()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Finishes progress.
     */
    public function finish()
    {
        $this->endTime = microtime(true);
    }

    /**
     * Returns complete time for running progress.
     * @return float Runtime of progress
     */
    public function getRuntime()
    {
        return $this->endTime - $this->startTime;
    }
}
