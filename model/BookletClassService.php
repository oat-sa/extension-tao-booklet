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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBooklet\model;

use common_report_Report;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Literal;
use core_kernel_classes_Resource as KernelResource;
use core_kernel_persistence_Exception;
use Exception;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\OntologyClassService;
use tao_models_classes_ClassService;
use core_kernel_classes_Resource;

class BookletClassService extends OntologyClassService
{
    use OntologyAwareTrait;

    public const CLASS_URI = 'http://www.tao.lu/Ontologies/Booklet.rdf#Booklet';
    public const PROPERTY_TEST = 'http://www.tao.lu/Ontologies/Booklet.rdf#Test';
    public const PROPERTY_DESCRIPTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#Description';
    public const PROPERTY_LAYOUT = 'http://www.tao.lu/Ontologies/Booklet.rdf#Layout';
    public const PROPERTY_COVER_PAGE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPage';
    public const PROPERTY_PAGE_HEADER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageHeader';
    public const PROPERTY_PAGE_FOOTER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageFooter';
    public const PROPERTY_FILE_CONTENT = 'http://www.tao.lu/Ontologies/Booklet.rdf#BookletFile';

    public const INSTANCE_LAYOUT_COVER = 'http://www.tao.lu/Ontologies/Booklet.rdf#LayoutCover';
    public const INSTANCE_LAYOUT_HEADER = 'http://www.tao.lu/Ontologies/Booklet.rdf#LayoutHeader';
    public const INSTANCE_LAYOUT_FOOTER = 'http://www.tao.lu/Ontologies/Booklet.rdf#LayoutFooter';
    public const INSTANCE_LAYOUT_ONE_PAGE_SECTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#OnePageSection';
    public const INSTANCE_LAYOUT_ONE_PAGE_ITEM = 'http://www.tao.lu/Ontologies/Booklet.rdf#OnePageItem';
    public const INSTANCE_LAYOUT_BLANK_PAGES = 'http://www.tao.lu/Ontologies/Booklet.rdf#BlankPages';
    public const INSTANCE_LAYOUT_BUBBLE_SHEET = 'http://www.tao.lu/Ontologies/Booklet.rdf#BubbleSheet';
    public const INSTANCE_LAYOUT_SHOW_RESPONSE_IDENTIFIER = 'http://www.tao.lu/Ontologies/Booklet.rdf#ShowResponseIdentifier';

    public const INSTANCE_COVER_PAGE_TITLE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageTitle';
    public const INSTANCE_COVER_PAGE_DESCRIPTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageDescription';
    public const INSTANCE_COVER_PAGE_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageDate';
    public const INSTANCE_COVER_PAGE_LOGO = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageLogo';
    public const INSTANCE_COVER_PAGE_QRCODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageQRCode';
    public const INSTANCE_COVER_PAGE_UNIQUE_ID = 'http://www.tao.lu/Ontologies/Booklet.rdf#CoverPageUniqueId';

    public const INSTANCE_PAGE_LOGO = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageLogo';
    public const INSTANCE_PAGE_TITLE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageTitle';
    public const INSTANCE_PAGE_MENTION = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageMention';
    public const INSTANCE_PAGE_LINK = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageLink';
    public const INSTANCE_PAGE_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageDate';
    public const INSTANCE_PAGE_NUMBER = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageNumber';
    public const INSTANCE_PAGE_UNIQUE_ID = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageUniqueId';
    public const INSTANCE_PAGE_QR_CODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageQRCode';

    public const INSTANCE_PAGE_EXPIRATION_DATE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageExpirationDate';
    public const INSTANCE_PAGE_SMALL_PRINT = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageSmallPrint';

    public const INSTANCE_PAGE_MATRIX_BARCODE = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageMatrixBarcode';
    public const INSTANCE_PAGE_SCAN_MARKS = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageScanMarks';

    public const INSTANCE_PAGE_CUSTOM_ID = 'http://www.tao.lu/Ontologies/Booklet.rdf#PageCustomId';

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
     * @param KernelClass $class
     * @param string $label
     * @param string $test
     * @return KernelResource
     * @throws Exception
     */
    public function createBookletInstance(KernelClass $class, string $label, string $test)
    {
        return $class->createInstanceWithProperties([
            OntologyRdfs::RDFS_LABEL => $label,
            self::PROPERTY_TEST => $test
        ]);
    }

    /**
     * Get the test linked to a booklet
     *
     * @param KernelResource $booklet
     *
     * @return KernelResource
     * @throws core_kernel_persistence_Exception
     */
    public function getTest(KernelResource $booklet): KernelResource
    {
        return $booklet->getOnePropertyValue($this->getProperty(self::PROPERTY_TEST));
    }

    /**
     * Get the attachment linked to a booklet
     *
     * @param KernelResource $booklet
     *
     * @return string
     * @throws core_kernel_persistence_Exception
     */
    public function getAttachment(KernelResource $booklet): string
    {
        return (string) $booklet->getOnePropertyValue($this->getProperty(self::PROPERTY_FILE_CONTENT));
    }

    /**
     * @param core_kernel_classes_Resource $instance
     * @param                              $tmpFile
     *
     * @param string                       $bookletLabel
     *
     * @return common_report_Report
     * @throws core_kernel_persistence_Exception
     */
    public function updateInstanceAttachment($instance, $tmpFile, string $bookletLabel = '')
    {
        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS);

        $this->removeInstanceAttachment($instance);

        /** @var StorageService $storageService */
        $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
        $property = $this->getProperty(self::PROPERTY_FILE_CONTENT);
        $serial = $storageService->storeFile($tmpFile);
        $instance->editPropertyValues($property, $serial);

        $report->setMessage(__('PDF File for booklet \'%s\' updated', $bookletLabel ?? $instance->getLabel()));
        $report->setData($serial);

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

        if ($contentUri && !($contentUri instanceof core_kernel_classes_Literal)) {
            $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
            $storageService->deleteFile($contentUri);
        }
    }
}
