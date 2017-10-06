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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 *
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */

namespace oat\taoBooklet\model;

use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;

class BookletConfigService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/BookletConfigService';

    const OPTION_DEFAULT_VALUES = 'default_values';
    const OPTION_MENTION = 'mention'; // string
    const OPTION_LINK = 'link';       // string
    const OPTION_LOGO = 'logo';       // string

    // time related
    const OPTION_DATE_FORMAT       = 'date_format';       // everything that works with DateTime::format(), default d/m/Y
    const OPTION_EXPIRATION_PERIOD = 'expiration_period'; // everything that works with DateTime::add(), default null
    const OPTION_EXPIRATION_DATE   = 'expiration_date';

    // formats for sprintf()
    const OPTION_EXPIRATION_STRING = 'expiration_string'; // default ''
    const OPTION_UNIQUE_ID_STRING  = 'unique_id_string';  // default ''
    const OPTION_CREATION_STRING   = 'creation_string';   // default ''
    const OPTION_CUSTOM_ID_STRING  = 'custom_id_string';  // default ''

    const OPTION_SMALL_PRINT       = 'small_print';        // string
    const OPTION_MATRIX_BARCODE    = 'matrix_barcode';     // string
    const OPTION_CUSTOM_ID         = 'custom_id';          // string

    const OPTION_SCAN_MARK_SYMBOL  = 'scan_mark_symbol';   // string

    const OPTION_TABLE_THEME       = 'table_theme';        // url

    const CONFIG_REGULAR = 'regular';
    const CONFIG_LAYOUT = 'layout';
    const CONFIG_COVER_PAGE = 'cover_page';
    const CONFIG_PAGE_HEADER = 'page_header';
    const CONFIG_PAGE_FOOTER = 'page_footer';
    const CONFIG_ONE_PAGE_ITEM = 'one_page_item';
    const CONFIG_ONE_PAGE_SECTION = 'one_page_section';
    const CONFIG_BLANK_PAGES = 'add_blank_pages';
    const CONFIG_BUBBLE_SHEET = 'use_bubble_sheet';
    const CONFIG_TITLE = 'title';
    const CONFIG_DESCRIPTION = 'description';
    const CONFIG_DATE = 'date';
    const CONFIG_LOGO = 'logo';
    const CONFIG_QRCODE = 'qr_code';
    const CONFIG_QRCODE_DATA = 'qr_code_data';
    const CONFIG_URI = 'uri';
    const CONFIG_MENTION = 'mention';
    const CONFIG_LINK = 'link';
    const CONFIG_PAGE_NUMBER = 'page_number';
    const CONFIG_UNIQUE_ID = 'unique_id';
    const CONFIG_UNIQUE_ID_NO_FORMAT = 'unique_id_no_format';
    const CONFIG_PAGE_QR_CODE = 'page_qr_code';

    const CONFIG_EXPIRATION_DATE   = 'expiration_date';
    const CONFIG_EXPIRATION_DATE_NO_FORMAT   = 'expiration_date_no_format';
    const CONFIG_EXPIRATION_PERIOD = 'expiration_period';
    const CONFIG_EXPIRATION_STRING = 'expiration_string';
    const CONFIG_UNIQUE_ID_STRING  = 'unique_id_string';
    const CONFIG_CUSTOM_ID_STRING  = 'custom_id_string';
    const CONFIG_DATE_FORMAT       = 'date_format';

    const CONFIG_CREATION_STRING   = 'date_string';

    const CONFIG_SMALL_PRINT       = 'small_print';
    const CONFIG_MATRIX_BARCODE    = 'matrix_barcode';   // string
    const CONFIG_CUSTOM_ID         = 'custom_id';        // string
    const CONFIG_SCAN_MARKS        = 'scan_marks';       // boolean
    const CONFIG_SCAN_MARK_SYMBOL  = 'scan_mark_symbol'; // unicode string, default 'square'

    const CONFIG_TABLE_THEME       = 'table_theme';      // url

    const CONFIG_EXTERNAL_DATA_PROVIDER = 'external_data_provider';

    /**
     * Maps the properties to config names
     * @var array
     */
    protected $configMap = [
        // properties
        BookletClassService::PROPERTY_LAYOUT => self::CONFIG_LAYOUT,
        BookletClassService::PROPERTY_COVER_PAGE => self::CONFIG_COVER_PAGE,
        BookletClassService::PROPERTY_PAGE_HEADER => self::CONFIG_PAGE_HEADER,
        BookletClassService::PROPERTY_PAGE_FOOTER => self::CONFIG_PAGE_FOOTER,

        // values
        BookletClassService::INSTANCE_LAYOUT_COVER => self::CONFIG_COVER_PAGE,
        BookletClassService::INSTANCE_LAYOUT_HEADER => self::CONFIG_PAGE_HEADER,
        BookletClassService::INSTANCE_LAYOUT_FOOTER => self::CONFIG_PAGE_FOOTER,
        BookletClassService::INSTANCE_LAYOUT_ONE_PAGE_ITEM => self::CONFIG_ONE_PAGE_ITEM,
        BookletClassService::INSTANCE_LAYOUT_ONE_PAGE_SECTION => self::CONFIG_ONE_PAGE_SECTION,
        BookletClassService::INSTANCE_LAYOUT_BLANK_PAGES => self::CONFIG_BLANK_PAGES,
        BookletClassService::INSTANCE_LAYOUT_BUBBLE_SHEET => self::CONFIG_BUBBLE_SHEET,

        BookletClassService::INSTANCE_COVER_PAGE_TITLE => self::CONFIG_TITLE,
        BookletClassService::INSTANCE_COVER_PAGE_DESCRIPTION => self::CONFIG_DESCRIPTION,
        BookletClassService::INSTANCE_COVER_PAGE_DATE => self::CONFIG_DATE,
        BookletClassService::INSTANCE_COVER_PAGE_LOGO => self::CONFIG_LOGO,
        BookletClassService::INSTANCE_COVER_PAGE_QRCODE => self::CONFIG_QRCODE,
        BookletClassService::INSTANCE_COVER_PAGE_UNIQUE_ID => self::CONFIG_UNIQUE_ID,

        BookletClassService::INSTANCE_PAGE_LOGO => self::CONFIG_LOGO,
        BookletClassService::INSTANCE_PAGE_TITLE => self::CONFIG_TITLE,
        BookletClassService::INSTANCE_PAGE_MENTION => self::CONFIG_MENTION,
        BookletClassService::INSTANCE_PAGE_LINK => self::CONFIG_LINK,
        BookletClassService::INSTANCE_PAGE_DATE => self::CONFIG_DATE,
        BookletClassService::INSTANCE_PAGE_NUMBER => self::CONFIG_PAGE_NUMBER,
        BookletClassService::INSTANCE_PAGE_UNIQUE_ID => self::CONFIG_UNIQUE_ID,
        BookletClassService::INSTANCE_PAGE_QR_CODE => self::CONFIG_PAGE_QR_CODE,

        BookletClassService::INSTANCE_PAGE_EXPIRATION_DATE => self::CONFIG_EXPIRATION_DATE,

        BookletClassService::INSTANCE_PAGE_SMALL_PRINT => self::CONFIG_SMALL_PRINT,
        BookletClassService::INSTANCE_PAGE_MATRIX_BARCODE => self::CONFIG_MATRIX_BARCODE,
        BookletClassService::INSTANCE_PAGE_SCAN_MARKS => self::CONFIG_SCAN_MARKS,
        BookletClassService::INSTANCE_PAGE_CUSTOM_ID => self::CONFIG_CUSTOM_ID
    ];

    /**
     * Set the default values onto the provided form
     * @param \tao_helpers_form_Form $form
     */
    public function setDefaultFormValues(\tao_helpers_form_Form $form)
    {
        $defaultValues = $this->getOption(self::OPTION_DEFAULT_VALUES);
        if (is_array($defaultValues)) {
            foreach($defaultValues as $option => $value) {
                $formElt = $form->getElement(\tao_helpers_Uri::encode($option));
                if ($formElt) {
                    if (!$formElt->getRawValue()) {
                        if (is_array($value)) {
                            foreach($value as $val) {
                                $formElt->setValue($val);
                            }
                        } else {
                            $formElt->setValue($value);
                        }
                    }
                }
            }
        }
    }

    /**
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @param core_kernel_classes_Resource|array $instance
     * @return array
     * @throws \common_exception_InvalidArgumentType
     */
    public function getConfig($instance)
    {
        if ($instance instanceof core_kernel_classes_Resource) {
            $properties = $instance->getPropertiesValues([
                OntologyRdfs::RDFS_LABEL,
                BookletClassService::PROPERTY_DESCRIPTION,
                BookletClassService::PROPERTY_LAYOUT,
                BookletClassService::PROPERTY_COVER_PAGE,
                BookletClassService::PROPERTY_PAGE_HEADER,
                BookletClassService::PROPERTY_PAGE_FOOTER
            ]);
        } else if (is_array($instance)) {
            $properties = $instance;
        } else {
            throw new \common_exception_InvalidArgumentType(
                'BookletConfigService',
                'getConfig',
                0,
                'core_kernel_classes_Resource',
                $instance
            );
        }

        $uniqueId = strtoupper(dechex(crc32(uniqid(microtime(), true))));

        $config = [
            self::CONFIG_LAYOUT => [],
            self::CONFIG_COVER_PAGE => [],
            self::CONFIG_PAGE_HEADER => [],
            self::CONFIG_PAGE_FOOTER => [],
            self::CONFIG_MENTION => $this->getOption(self::OPTION_MENTION),
            self::CONFIG_LINK => $this->getOption(self::OPTION_LINK),
            self::CONFIG_LOGO => $this->getOption(self::OPTION_LOGO),
            self::CONFIG_DATE => $this->formatValue(
                $this->getOption(self::OPTION_CREATION_STRING),
                $this->getDate()
            ),
            self::CONFIG_REGULAR => false,
            self::CONFIG_TITLE => $this->getPropertyValue($properties, OntologyRdfs::RDFS_LABEL),
            self::CONFIG_DESCRIPTION => $this->getPropertyValue($properties, BookletClassService::PROPERTY_DESCRIPTION),

            self::CONFIG_UNIQUE_ID_NO_FORMAT => $uniqueId,
            self::CONFIG_UNIQUE_ID => $this->formatValue($this->getOption(self::OPTION_UNIQUE_ID_STRING), $uniqueId),

            self::CONFIG_SMALL_PRINT => $this->getOption(self::OPTION_SMALL_PRINT),

            self::CONFIG_EXPIRATION_DATE_NO_FORMAT => $this->getDate($this->getOption(self::OPTION_EXPIRATION_PERIOD)),
            self::CONFIG_EXPIRATION_DATE => $this->formatValue(
                $this->getOption(self::OPTION_EXPIRATION_STRING),
                $this->getDate($this->getOption(self::OPTION_EXPIRATION_PERIOD))
            ),
            self::CONFIG_SCAN_MARK_SYMBOL => $this->getScanMarkSymbol(),
            self::CONFIG_TABLE_THEME => $this->getOption(self::OPTION_TABLE_THEME)
        ];

        if (isset($properties[BookletClassService::PROPERTY_LAYOUT])) {
            $config[self::CONFIG_LAYOUT] = $this->getConfigSet($properties[BookletClassService::PROPERTY_LAYOUT]);
        }
        if (isset($properties[BookletClassService::PROPERTY_COVER_PAGE])) {
            $config[self::CONFIG_COVER_PAGE] = $this->getConfigSet($properties[BookletClassService::PROPERTY_COVER_PAGE]);
        }
        if (isset($properties[BookletClassService::PROPERTY_PAGE_HEADER])) {
            $config[self::CONFIG_PAGE_HEADER] = $this->getConfigSet($properties[BookletClassService::PROPERTY_PAGE_HEADER]);
        }
        if (isset($properties[BookletClassService::PROPERTY_PAGE_FOOTER])) {
            $config[self::CONFIG_PAGE_FOOTER] = $this->getConfigSet($properties[BookletClassService::PROPERTY_PAGE_FOOTER]);
        }

        $externalDataProviderClass = $this->getOption(self::CONFIG_EXTERNAL_DATA_PROVIDER);
        if($externalDataProviderClass && class_exists($externalDataProviderClass)) {
            $externalDataProvider = new $externalDataProviderClass($config, $properties);
            $config[self::CONFIG_MATRIX_BARCODE] = $externalDataProvider->getMatrixBarcodeData();
            $config[self::CONFIG_CUSTOM_ID] = $this->formatValue(
                $this->getOption(self::OPTION_CUSTOM_ID_STRING),
                $externalDataProvider->getCustomId()
            );
        }

        if ($instance instanceof core_kernel_classes_Resource) {
            $config[self::CONFIG_URI] = $instance->getUri();
        }

        return $config;
    }

    /**
     * Format dates and such with sprintf() if applicable
     *
     * @param $format
     * @param $value
     *
     * @return string
     */
    protected function formatValue($format, $value) {
        if(!$value) {
            return '';
        }
        return $format ? sprintf($format, $value) : $value;
    }


    /**
     * Create basic date in the provided format
     *
     * @param null $period
     *
     * @return string
     */
    protected function getDate($period=null) {
        if(!$this->getOption(self::OPTION_EXPIRATION_PERIOD)
            || !$this->getOption(self::OPTION_EXPIRATION_STRING)) {
            return false;
        }
        $format = $this->getOption(self::OPTION_DATE_FORMAT);
        $dateObj = new \DateTime();
        if($period) {
            $dateObj->add(\DateInterval::createFromDateString($period));
        }
        return $dateObj->format($format ? $format : 'd/m/Y');
    }

    /**
     * The symbol that is used as a scan mark if any.
     * This is a reference to taoBooklet/views/templates/PrintTest/line.js::addScanMarks
     *
     * @return string
     */
    protected function getScanMarkSymbol() {
        $scanMarkSymbol = $this->getOption(self::OPTION_SCAN_MARK_SYMBOL);
        return $scanMarkSymbol ? $scanMarkSymbol : 'square';
    }


    /**
     * Gets the value from a list of properties
     * @param array $properties
     * @param string $key
     * @return null|string
     */
    protected function getPropertyValue($properties, $key)
    {
        if (isset($properties[$key])) {
            $value = $properties[$key];
            if (is_array($value)) {
                $value = current($properties[$key]);
            }
            return (string)$value;
        }
        return null;
    }

    /**
     * Translates a list of resource properties into a list of named boolean values
     * @param array $properties
     * @return array
     */
    protected function getConfigSet($properties)
    {
        $config = [];

        foreach($properties as $property) {
            if ($property instanceof core_kernel_classes_Resource) {
                $uri = $property->getUri();
            } else {
                $uri = $property;
            }
            if (isset($this->configMap[$uri])) {
                $config[$this->configMap[$uri]] = true;
            }
        }

        return $config;
    }
}
