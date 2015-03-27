<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBooklet\model;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_fileSystem_FileSystem;
use common_ext_ExtensionsManager;
use core_kernel_versioning_File;

class StorageService
{
    const CONFIG_KEY = 'bookletStorage';

    /**
     * @param string $filePath
     *
     * @return core_kernel_versioning_File
     */
    static public function storeFile( $filePath )
    {
        return self::getFileSystem()->spawnFile( $filePath );
    }

    /**
     * Removes file from FS attached to instance
     *
     * @param $instance core_kernel_classes_Resource
     */
    static public function removeAttachedFile( core_kernel_classes_Resource $instance )
    {
        if ( ! is_null( $instance )) {
            $contentUri = $instance->getOnePropertyValue(
                new core_kernel_classes_Property( BookletClassService::PROPERTY_FILE_CONTENT )
            );

            if ($contentUri instanceof core_kernel_classes_Resource) {
                $file = new core_kernel_versioning_File( $contentUri );
                $file->delete();
            }
        }

        \core_kernel_fileSystem_Cache::flushCache();
    }

    /**
     *
     * @return \core_kernel_fileSystem_FileSystem
     */
    static public function getFileSystem()
    {
        $uri = common_ext_ExtensionsManager::singleton()->getExtensionById( 'taoBooklet' )->getConfig(
            self::CONFIG_KEY
        );

        return new core_kernel_fileSystem_FileSystem( $uri );
    }

    static public function setFileSystem( core_kernel_fileSystem_FileSystem $fs )
    {
        common_ext_ExtensionsManager::singleton()->getExtensionById( 'taoBooklet' )->setConfig(
            self::CONFIG_KEY,
            $fs->getUri()
        );
    }
}