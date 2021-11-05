<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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

namespace OpusTest\Console\Helper;

use Opus\Console\Helper\ProgressBar;
use OpusTest\TestAsset\TestCase;
use Symfony\Component\Console\Output\StreamOutput;

use function fopen;
use function rewind;
use function str_repeat;
use function stream_get_contents;

class ProgressBarTest extends TestCase
{
    public function testProgressBarWidth()
    {
        $outputInterface = $this->createOutputInterface();

        $progress = new ProgressBar($outputInterface, 100);

        $progress->start();
        $progress->setProgress(0);

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $bar = '[>' . str_repeat('-', 62) . ']'; // 62 because max = 100 (3 digits)

        $this->assertContains($bar, $output); // two spaces before 10
        $this->assertContains('0/100', $output);
    }

    /**
     * @return StreamOutput
     */
    protected function createOutputInterface()
    {
        return new StreamOutput(fopen('php://memory', 'r+', false));
    }
}
