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
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\fileReference\ResourceFileSerializer;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;

class StorageService
{
    const FILE_SYSTEM_ID = 'bookletStorage';

    /**
     * @param string $filePath
     * @return File
     */
    static public function storeFile($filePath)
    {
        $newFileName = \helpers_File::createFileName($filePath);

        /** @var File $file */
        $file = ServiceManager::getServiceManager()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory(self::FILE_SYSTEM_ID)
            ->getFile($newFileName);

        $stream = fopen($filePath, 'r+');
        $file->write($stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $file;
    }

    /**
     * Removes file from FS attached to instance
     *
     * @param core_kernel_classes_Resource $instance
     */
    static public function removeAttachedFile(core_kernel_classes_Resource $instance)
    {
        $contentUri = $instance->getOnePropertyValue(
            new core_kernel_classes_Property(BookletClassService::PROPERTY_FILE_CONTENT)
        );

        if ($contentUri) {
            $file = self::getFileReferenceSerializer()->unserializeFile($contentUri);
            $file->delete();
        }
    }

    /**
     * Get serializer to persist filesystem object
     * @return FileReferenceSerializer
     */
    static protected function getFileReferenceSerializer()
    {
        return ServiceManager::getServiceManager()->get(ResourceFileSerializer::SERVICE_ID);
    }
}