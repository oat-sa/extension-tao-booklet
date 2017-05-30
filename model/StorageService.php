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

use core_kernel_classes_Resource;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;

class StorageService extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoBooklet/bookletStorage';

    const FILE_SYSTEM_ID = 'bookletStorage';

    /**
     * @param string $filePath
     * @return core_kernel_classes_Resource
     */
    public function storeFile($filePath)
    {
        $file = $this->getFileSystem()->getFile($this->createFileName($filePath));

        $stream = fopen($filePath, 'r+');
        $file->put($stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
        
        return $this->getResource($this->getFileReferenceSerializer()->serialize($file));
    }

    /**
     * @param core_kernel_classes_Resource $fileResource
     * @return File
     */
    public function getFile($fileResource)
    {
        $fileResource = $this->getResource($fileResource);
        if ($fileResource) {
            return $this->getFileReferenceSerializer()->unserializeFile($fileResource->getUri());
        }
        return null;
    }

    /**
     * @param core_kernel_classes_Resource $fileResource
     */
    public function deleteFile($fileResource)
    {
        $fileResource = $this->getResource($fileResource);
        if ($fileResource) {
            $this->getFile($fileResource)->delete();
            $fileResource->delete();
        }
    }

    /**
     *
     * @return \oat\oatbox\filesystem\Directory
     */
    protected function getFileSystem()
    {
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID)->getDirectory(self::FILE_SYSTEM_ID);
    }

    /**
     * Get serializer to persist filesystem object
     *
     * @return FileReferenceSerializer
     */
    protected function getFileReferenceSerializer()
    {
        return $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);
    }

    /**
     * Create a unique file name on basis of the original one.
     * @param string $originalName
     * @return string
     */
    protected function createFileName($originalName)
    {
        $returnValue = uniqid(hash('crc32', $originalName));

        $ext = @pathinfo($originalName, PATHINFO_EXTENSION);
        if (!empty($ext)){
            $returnValue .= '.' . $ext;
        }

        return (string) $returnValue;
    }
}
