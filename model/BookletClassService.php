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
use core_kernel_classes_Property;

class BookletClassService extends tao_models_classes_ClassService
{
    const CLASS_URI = 'http://www.tao.lu/Ontologies/Booklet.rdf#Booklet';
    const PROPERTY_TEST = 'http://www.tao.lu/Ontologies/Booklet.rdf#Test';
    const PROPERTY_DESCRIPTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#Description';
    const PROPERTY_LAYOUT = 'http://www.tao.lu/Ontologies/Booklet.rdf#Layout';
    const PROPERTY_COVER_PAGE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPage';
    const PROPERTY_PAGE_HEADER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageHeader';
    const PROPERTY_PAGE_FOOTER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageFooter';
    const PROPERTY_FILE_CONTENT = 'http://www.tao.lu/Ontologies/Booklet.rdf#BookletFile';

    const INSTANCE_LAYOUT_COVER = 'http://www.tao.lu/Ontologies/Booklet.rdf#LayoutCover';
    const INSTANCE_LAYOUT_HEADER = 'http://www.tao.lu/Ontologies/Booklet.rdf#LayoutHeader';
    const INSTANCE_LAYOUT_FOOTER = 'http://www.tao.lu/Ontologies/Booklet.rdf#LayoutFooter';
    const INSTANCE_LAYOUT_ONE_PAGE_SECTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#OnePageSection';
    const INSTANCE_LAYOUT_ONE_PAGE_ITEM = 'http://www.tao.lu/Ontologies/Booklet.rdf#OnePageItem';

    const INSTANCE_COVER_PAGE_TITLE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageTitle';
    const INSTANCE_COVER_PAGE_DESCRIPTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageDescription';
    const INSTANCE_COVER_PAGE_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageDate';
    const INSTANCE_COVER_PAGE_LOGO = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageLogo';
    const INSTANCE_COVER_PAGE_QRCODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageQRCode';

    const INSTANCE_PAGE_LOGO = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageLogo';
    const INSTANCE_PAGE_TITLE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageTitle';
    const INSTANCE_PAGE_MENTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageMention';
    const INSTANCE_PAGE_LINK = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageLink';
    const INSTANCE_PAGE_NUMBER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageNumber';

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
     *
     * @return core_kernel_classes_Resource
     * @throws \Exception
     */
    public function createBookletInstance(core_kernel_classes_Class $class, $label, $test, $tmpFile) {

        $fileResource = StorageService::storeFile($tmpFile);

        if ($fileResource){
            $instance = $class->createInstanceWithProperties(array(
                RDFS_LABEL => $label,
                self::PROPERTY_FILE_CONTENT => $fileResource,
                self::PROPERTY_TEST => $test
            ));
        }else{
            throw new \Exception('No file found to attach');
        }


        return $instance;
    }

    /**
     * Get the test linked to a booklet
     * @param core_kernel_classes_Resource $booklet
     * @return core_kernel_classes_Resource
     */
    public function getTest(core_kernel_classes_Resource $booklet)
    {
       return $booklet->getOnePropertyValue(new core_kernel_classes_Property(self::PROPERTY_TEST));
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
