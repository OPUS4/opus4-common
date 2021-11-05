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

use Exception;
use Opus\Console\Helper\ProgressReport;
use Opus\Console\Helper\ProgressReportEntry;
use OpusTest\TestAsset\TestCase;
use Symfony\Component\Console\Output\StreamOutput;

use function fopen;
use function rewind;
use function stream_get_contents;
use function strlen;

use const PHP_EOL;

class ProgressReportTest extends TestCase
{
    public function testAddExceptionCreatesEntry()
    {
        $report = new ProgressReport();

        $this->assertNull($report->getCurrentEntry());

        $ex = new Exception('test');

        $report->addException($ex);

        $entry = $report->getCurrentEntry();

        $this->assertNotNull($entry);
        $this->assertInstanceOf(ProgressReportEntry::class, $entry);

        $report->finishEntry();

        $this->assertNull($report->getCurrentEntry());
    }

    public function testSetEntryInfo()
    {
        $report = new ProgressReport();

        $ex = new Exception('TestException');

        $report->addException($ex);
        $report->setEntryInfo('TestTitle', 'Test');

        $entry = $report->getCurrentEntry();

        $this->assertEquals('TestTitle', $entry->getTitle());
        $this->assertEquals('Test', $entry->getCategory());
        $this->assertCount(1, $entry->getExceptions());
    }

    public function testWriteSingleStepWithException()
    {
        $outputInterface = $this->createOutputInterface();

        $report = new ProgressReport();

        $ex = new Exception('TestException');

        $report->addException($ex);
        $report->setEntryInfo('TestTitle', 'Test');

        $report->write($outputInterface);

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $this->assertContains('There was 1 document with problems:', $output);
        $this->assertContains('1) TestTitle' . PHP_EOL, $output);
        $this->assertContains('TestException' . PHP_EOL, $output);
    }

    public function testWriteMultipleStepsWithException()
    {
        $outputInterface = $this->createOutputInterface();

        $report = new ProgressReport();

        $ex = new Exception('TestException');

        $report->addException($ex);
        $report->setEntryInfo('TestTitle', 'Test');
        $report->finishEntry();

        $ex = new Exception('TestException2');

        $report->addException($ex);
        $report->setEntryInfo('TestTitle2', 'Test');
        $report->finishEntry();

        $report->write($outputInterface);

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $this->assertContains('There were 2 documents with problems:', $output);
        $this->assertContains('1) TestTitle' . PHP_EOL, $output);
        $this->assertContains('TestException' . PHP_EOL, $output);
        $this->assertContains('2) TestTitle2' . PHP_EOL, $output);
        $this->assertContains('TestException2' . PHP_EOL, $output);
    }

    public function testWriteNothingIfNotEntries()
    {
        $outputInterface = $this->createOutputInterface();

        $report = new ProgressReport();

        $report->write($outputInterface);

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $this->assertTrue(strlen($output) === 0);
    }

    public function testMultipleExceptionsForEntry()
    {
        $outputInterface = $this->createOutputInterface();

        $report = new ProgressReport();

        $report->addException(new Exception('TestException'));
        $report->addException(new Exception('TestException2'));
        $report->setEntryInfo('TestTitle', 'Test');

        $report->write($outputInterface);

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $this->assertContains('There was 1 document with problems:', $output);
        $this->assertContains('1) TestTitle' . PHP_EOL, $output);
        $this->assertContains('TestException' . PHP_EOL . 'TestException2' . PHP_EOL, $output);
    }

    public function testClear()
    {
        $outputInterface = $this->createOutputInterface();

        $report = new ProgressReport();

        $ex = new Exception('TestException');

        $report->addException($ex);
        $report->setEntryInfo('TestTitle', 'Test');
        $report->clear();

        $report->write($outputInterface);

        rewind($outputInterface->getStream());
        $output = stream_get_contents($outputInterface->getStream());

        $this->assertTrue(strlen($output) === 0);
    }

    /**
     * @return StreamOutput
     */
    protected function createOutputInterface()
    {
        return new StreamOutput(fopen('php://memory', 'r+', false));
    }
}
