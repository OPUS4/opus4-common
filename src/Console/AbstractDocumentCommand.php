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

namespace Opus\Common\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function ctype_digit;
use function is_int;
use function mb_split;

/**
 * Base class for all commands using StartID and EndID as arguments.
 */
abstract class AbstractDocumentCommand extends Command
{
    public const ARGUMENT_START_ID = 'StartID';

    public const ARGUMENT_END_ID = 'EndID';

    /** @var bool */
    private $allDocuments = false;

    /** @var int */
    protected $startId;

    /** @var int */
    protected $endId;

    /** @var bool */
    private $singleDocument = false;

    /** @var string */
    protected $startIdDescription = 'ID of document where processing should start (or \'-\')';

    /** @var string */
    protected $endIdDescription = 'ID of document where processing should stop (or \'-\')';

    protected function configure()
    {
        $this->addArgument(
            self::ARGUMENT_START_ID,
            InputArgument::OPTIONAL,
            $this->startIdDescription
        )
        ->addArgument(
            self::ARGUMENT_END_ID,
            InputArgument::OPTIONAL,
            $this->endIdDescription
        );
    }

    /**
     * @return int
     */
    protected function processArguments(InputInterface $input)
    {
        $startId = $input->getArgument(self::ARGUMENT_START_ID);
        $endId   = $input->getArgument(self::ARGUMENT_END_ID);

        // handle accidental inputs like '20-' or '20-30' instead of '20 -' or '20 30'
        if ($startId !== '-' && $startId !== null) {
            $parts = mb_split('-', $startId);
            if (count($parts) === 2) {
                $startId = $parts[0];
                $endId   = $parts[1];

                if ($endId === '') {
                    $endId = '-'; // otherwise only a single document will be indexed
                }
            }
        }

        if ($startId === '-' || $startId === '' || $startId === null) {
            $startId = null;
        } else {
            // only activate single document indexing if startId is present and no endId
            if ($endId === '' || $endId === null) {
                $this->singleDocument = true;
                $endId                = null;
            }
        }

        if ($endId === '-') {
            $endId = null;
        }

        if (! is_int($startId) && $startId !== null && ! ctype_digit($startId)) {
            throw new InvalidArgumentException('StartID needs to be an integer.');
        }

        if (! is_int($endId) && $endId !== null && ! ctype_digit($endId)) {
            throw new InvalidArgumentException('EndID needs to be an integer.');
        }

        if ($startId === null && $endId === null) {
            $this->allDocuments = true;
        } else {
            $this->allDocuments = false;

            if ($startId !== null && $endId !== null && $startId > $endId) {
                $tmp     = $startId;
                $startId = $endId;
                $endId   = $tmp;
            }
        }

        $this->startId = (int) $startId;
        $this->endId   = (int) $endId;

        return 0;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->processArguments($input);
    }

    /**
     * @return bool
     */
    protected function isSingleDocument()
    {
        return $this->singleDocument;
    }

    /**
     * @return bool
     */
    protected function isAllDocuments()
    {
        return $this->allDocuments;
    }
}
