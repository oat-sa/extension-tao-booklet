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
 *               
 * 
 */

namespace oat\taoBooklet\model;

use tao_models_classes_ClassService;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;

class BookletClassService extends tao_models_classes_ClassService
{
    const CLASS_URI = 'http://www.tao.lu/Ontologies/Booklet.rdf#Booklet';
    
    const PROPERTY_FILE_CONTENT = 'http://www.tao.lu/Ontologies/Booklet.rdf#BookletFile';

    /**
     * (non-PHPdoc)
     * 
     * @see tao_models_classes_ClassService::getRootClass()
     */
    public function getRootClass()
    {
        return new core_kernel_classes_Class(self::CLASS_URI);
    }

    /**
     * 
     * @param core_kernel_classes_Class $class
     * @param string $label
     * @param string $test
     * @param string $tmpFile
     * @return core_kernel_classes_Resource
     */
    public function createBookletInstance(core_kernel_classes_Class $class, $label, $test, $tmpFile) {
        
        $fileResource = StorageService::storeFile($tmpFile);

        $instance = $class->createInstanceWithProperties(array(
            RDFS_LABEL => $label,
            self::PROPERTY_FILE_CONTENT => $fileResource,
            INSTANCE_TEST_MODEL_QTI => $test
        ));

        return $instance;
    }

    /**
     * @param core_kernel_classes_Resource $instance
     * @param $tmpFile
     *
     * @return \common_report_Report
     */
    public function updateInstanceAttachment($instance, $tmpFile){
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS);

        StorageService::removeAttachedFile( $instance );
        $fileResource = StorageService::storeFile($tmpFile);
        $property = new \core_kernel_classes_Property(self::PROPERTY_FILE_CONTENT);
        $instance->editPropertyValues($property, $fileResource);

        $report->setMessage(__('%s updated', $instance->getLabel()));
        $report->setData($instance);
        return $report;
    }
}