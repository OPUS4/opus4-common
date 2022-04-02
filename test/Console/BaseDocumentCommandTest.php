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

namespace OpusTest\Common\Console;

use InvalidArgumentException;
use Opus\Common\Console\AbstractBaseDocumentCommand;
use OpusTest\Common\TestAsset\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

class BaseDocumentCommandTest extends TestCase
{
    /**
     * @return array Columns: argStartId, argEndId, startId, endId, singleDocument
     */
    public function argumentsProvider()
    {
        return [
            [null,    null, null, null, false],
            ['10',    null,   10, null, true],
            ['10',    '20',   10,   20, false],
            ['-10',   null, null,   10, false],
            ['10-',   null,   10, null, false],
            ['10-25', null,   10,   25, false],
            ['45',     '-',   45, null, false],
            ['-',     '80', null,   80, false],
            ['-',     null, null, null, false],
            ['-',      '-', null, null, false],
        ];
    }

    /**
     * @param mixed    $argStartId
     * @param mixed    $argEndId
     * @param null|int $startId
     * @param null|int $endId
     * @param bool     $singleDocument
     * @dataProvider argumentsProvider
     */
    public function testDocumentRangeArguments($argStartId, $argEndId, $startId, $endId, $singleDocument)
    {
        $commandClass = AbstractBaseDocumentCommand::class;

        $stub = $this->getMockForAbstractClass($commandClass);
        $stub->setName('test');

        $tester = new CommandTester($stub);
        $tester->execute([
            AbstractBaseDocumentCommand::ARGUMENT_START_ID => $argStartId,
            AbstractBaseDocumentCommand::ARGUMENT_END_ID   => $argEndId,
        ]);

        $ref = new ReflectionClass($commandClass);

        $refStartId = $ref->getProperty('startId');
        $refStartId->setAccessible(true);

        $refEndId = $ref->getProperty('endId');
        $refEndId->setAccessible(true);

        $refSingleDocument = $ref->getProperty('singleDocument');
        $refSingleDocument->setAccessible(true);

        $this->assertEquals($startId, $refStartId->getValue($stub));
        $this->assertEquals($endId, $refEndId->getValue($stub));
        $this->assertEquals($singleDocument, $refSingleDocument->getValue($stub));
    }

    /**
     * @return array
     */
    public function invalidArgumentProvider()
    {
        return [
            ['a',   null, 'StartID needs to be an integer'],
            ['1.2', null, 'StartID needs to be an integer'],
            ['a',    'b', 'StartID needs to be an integer'],
            ['10',   'b', 'EndID needs to be an integer'],
            ['10',   '*', 'EndID needs to be an integer'],
        ];
    }

    /**
     * @param int    $argStartId
     * @param int    $argEndId
     * @param string $message
     * @dataProvider invalidArgumentProvider
     */
    public function testInvalidArguments($argStartId, $argEndId, $message)
    {
        $commandClass = AbstractBaseDocumentCommand::class;

        $stub = $this->getMockForAbstractClass($commandClass);
        $stub->setName('test');

        $tester = new CommandTester($stub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $tester->execute([
            AbstractBaseDocumentCommand::ARGUMENT_START_ID => $argStartId,
            AbstractBaseDocumentCommand::ARGUMENT_END_ID   => $argEndId,
        ]);
    }
}
