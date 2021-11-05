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
 * @category    Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Console\Helper;

use Opus\Console\Helper\ProgressMatrix;
use OpusTest\TestAsset\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;

class ProgressMatrixTest extends TestCase
{

    public function testAdvance()
    {
        $outputInterface = $this->createOutputInterface();

        $max = 100;

        $progress = new ProgressMatrix($outputInterface, $max);

        $progress->start();

        for ($i = 0; $i < $max; $i++) {
            $progress->advance();
        }

        $progress->finish();

        rewind($outputInterface->getStream());

        $output = stream_get_contents($outputInterface->getStream());

        $this->assertContains('.   63 / 100 ( 63%)' . PHP_EOL, $output); // end of first line
        $this->assertContains(str_repeat('.', 63), $output); // first line
        $this->assertContains(PHP_EOL . str_repeat('.', 37), $output); // second line
    }

    public function testAdvanceStepLargerThanOne()
    {
        $outputInterface = $this->createOutputInterface();

        $max = 100;

        $progress = new ProgressMatrix($outputInterface, $max);

        $progress->start();

        for ($i = 0; $i < $max; $i += 6) {
            $progress->setProgress($i);
        }
        $progress->setProgress($max); // work finished

        $progress->finish();

        rewind($outputInterface->getStream());

        $output = stream_get_contents($outputInterface->getStream());

        $this->assertContains('.   63 / 100 ( 63%)' . PHP_EOL, $output); // end of first line
        $this->assertContains(str_repeat('.', 63), $output); // first line
        $this->assertContains(PHP_EOL . str_repeat('.', 37), $output); // second line
    }

    public function testSetProgress()
    {
        $outputInterface = $this->createOutputInterface();

        $max = 100;

        $progress = new ProgressMatrix($outputInterface, $max);

        $progress->start();

        $count = 0;

        for ($i = 0; $i < $max; $i++) {
            $count++;
            $progress->setProgress($count);
        }

        $progress->finish();

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $this->assertContains('.   63 / 100 ( 63%)' . PHP_EOL, $output); // end of first line
        $this->assertContains(str_repeat('.', 63), $output); // first line
        $this->assertContains(PHP_EOL . str_repeat('.', 37), $output); // second line
    }

    public function testLineLengthAdjustsToMax()
    {
        // TODO generate output for first line OR check calculation of maxLineLength
        $this->markTestIncomplete(); // TODO test
    }

    public function testShowStepState()
    {
        $this->markTestIncomplete('test showing different step states'); // TODO test
    }

    /**
     * @return StreamOutput
     */
    protected function createOutputInterface()
    {
        return new StreamOutput(fopen('php://memory', 'r+', false));
    }
}
