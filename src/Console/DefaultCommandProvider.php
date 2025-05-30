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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Console;

use Opus\Common\ConfigTrait;
use Opus\Common\LoggingTrait;
use Opus\Common\Util\ClassLoaderHelper;
use Symfony\Component\Console\Command\Command;

use function array_merge;

/**
 * The default CommandProvider looks for CommandProviders in the configuration
 * and collects all the commands to return them.
 */
class DefaultCommandProvider implements CommandProviderInterface
{
    use ConfigTrait;
    use LoggingTrait;

    /**
     * @return Command[]
     */
    public function getCommands()
    {
        $commands = [];

        $providers = $this->getCommandProviders();

        foreach ($providers as $provider) {
            $providerCommands = $provider->getCommands();
            if ($providerCommands !== null) {
                $commands = array_merge($commands, $providerCommands);
            }
        }

        return $commands;
    }

    /**
     * @return CommandProviderInterface[]
     */
    public function getCommandProviders()
    {
        $config = $this->getConfig();

        $providers = [];

        if (isset($config->console->commandProvider)) {
            $providerClasses = $config->console->commandProvider->toArray();
            foreach ($providerClasses as $providerClass) {
                if (ClassLoaderHelper::classExists($providerClass)) {
                    $provider    = new $providerClass();
                    $providers[] = $provider;
                } else {
                    $this->getLogger()->err('Command provider class not found: ' . $providerClass);
                }
            }
        }

        return $providers;
    }
}
