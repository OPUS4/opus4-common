<?php

/**
 * LICENCE
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @copyright   Copyright (c) 2009-2010
 *              Saechsische Landesbibliothek - Staats- und Universitaetsbibliothek Dresden (SLUB)
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Model\Plugin;

use Opus\Common\Model\ModelInterface;

/**
 * Abstract class implementing method stubs for Opus_Model_Plugin_Interface
 * as a convinience to the plugin developer. It's intentionally declared abstract
 * to not allow usage as a plugin directly.
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @see {PluginInterface::preStore}
     */
    public function preStore(ModelInterface $model)
    {
    }

    /**
     * @see {PluginInterface::preFetch}
     */
    public function preFetch(ModelInterface $model)
    {
    }

    /**
     * @see {PluginInterface::postStore}
     */
    public function postStore(ModelInterface $model)
    {
    }

    /**
     * @see {PluginInterface::postStoreInternal}
     */
    public function postStoreInternal(ModelInterface $model)
    {
    }

    /**
     * @see {PluginInterface::postStoreExternal}
     */
    public function postStoreExternal(ModelInterface $model)
    {
    }

    /**
     * @see {PluginInterface::preDelete}
     */
    public function preDelete(ModelInterface $model)
    {
    }

    /**
     * @see {PluginInterface::postDelete}
     *
     * @param mixed $modelId
     */
    public function postDelete($modelId)
    {
    }
}
