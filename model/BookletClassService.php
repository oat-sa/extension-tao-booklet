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

use oat\generis\model\OntologyAwareTrait;
use tao_models_classes_ClassService;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;

class BookletClassService extends tao_models_classes_ClassService
{
    use OntologyAwareTrait;

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
    const INSTANCE_LAYOUT_BLANK_PAGES = 'http://www.tao.lu/Ontologies/Booklet.rdf#BlankPages';
    const INSTANCE_LAYOUT_BUBBLE_SHEET = 'http://www.tao.lu/Ontologies/Booklet.rdf#BubbleSheet';
    const INSTANCE_LAYOUT_SHOW_RESPONSE_IDENTIFIER = 'http://www.tao.lu/Ontologies/Booklet.rdf#ShowResponseIdentifier';

    const INSTANCE_COVER_PAGE_TITLE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageTitle';
    const INSTANCE_COVER_PAGE_DESCRIPTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageDescription';
    const INSTANCE_COVER_PAGE_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageDate';
    const INSTANCE_COVER_PAGE_LOGO = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageLogo';
    const INSTANCE_COVER_PAGE_QRCODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageQRCode';
    const INSTANCE_COVER_PAGE_UNIQUE_ID = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageUniqueId';

    const INSTANCE_PAGE_LOGO = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageLogo';
    const INSTANCE_PAGE_TITLE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageTitle';
    const INSTANCE_PAGE_MENTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageMention';
    const INSTANCE_PAGE_LINK = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageLink';
    const INSTANCE_PAGE_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageDate';
    const INSTANCE_PAGE_NUMBER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageNumber';
    const INSTANCE_PAGE_UNIQUE_ID = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageUniqueId';
    const INSTANCE_PAGE_QR_CODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageQRCode';

    const INSTANCE_PAGE_EXPIRATION_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageExpirationDate';
    const INSTANCE_PAGE_SMALL_PRINT = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageSmallPrint';

    const INSTANCE_PAGE_MATRIX_BARCODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageMatrixBarcode';
    const INSTANCE_PAGE_SCAN_MARKS = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageScanMarks';

    const INSTANCE_PAGE_CUSTOM_ID = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageCustomId';

    /**
     * (non-PHPdoc)
     *
     * @see tao_models_classes_ClassService::getRootClass()
     */
    public function getRootClass()
    {
        return $this->getClass(self::CLASS_URI);
    }

    /**
     *
     * @param core_kernel_classes_Class $class
     * @param string $label
     * @param string $test
     * @return core_kernel_classes_Resource
     * @throws \Exception
     */
    public function createBookletInstance(core_kernel_classes_Class $class, $label, $test)
    {
        return $class->createInstanceWithProperties(array(
            RDFS_LABEL => $label,
            self::PROPERTY_TEST => $test
        ));
    }

    /**
     * Get the test linked to a booklet
     * @param core_kernel_classes_Resource $booklet
     * @return core_kernel_classes_Resource
     */
    public function getTest(core_kernel_classes_Resource $booklet)
    {
        return $booklet->getOnePropertyValue($this->getProperty(self::PROPERTY_TEST));
    }

    /**
     * Get the attachment linked to a booklet
     * @param core_kernel_classes_Resource $booklet
     * @return core_kernel_classes_Resource
     */
    public function getAttachment(core_kernel_classes_Resource $booklet)
    {
        return $booklet->getOnePropertyValue($this->getProperty(self::PROPERTY_FILE_CONTENT));
    }

    /**
     * @param core_kernel_classes_Resource $instance
     * @param $tmpFile
     *
     * @return \common_report_Report
     */
    public function updateInstanceAttachment($instance, $tmpFile)
    {
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS);

        $this->removeInstanceAttachment($instance);

        $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
        $property = $this->getProperty(self::PROPERTY_FILE_CONTENT);
        $instance->editPropertyValues($property, $storageService->storeFile($tmpFile));

        $report->setMessage(__('%s updated', $instance->getLabel()));
        $report->setData($instance);
        return $report;
    }

    /**
     * Removes file from FS attached to instance
     *
     * @param core_kernel_classes_Resource $instance
     */
    public function removeInstanceAttachment(core_kernel_classes_Resource $instance)
    {
        $property = $this->getProperty(self::PROPERTY_FILE_CONTENT);
        $contentUri = $instance->getOnePropertyValue($property);
        
        if ($contentUri && !($contentUri instanceof \core_kernel_classes_Literal)) {
            $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
            $storageService->deleteFile($contentUri);
        }
    }
}
