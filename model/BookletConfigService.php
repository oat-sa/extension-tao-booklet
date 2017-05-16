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
use oat\oatbox\service\ConfigurableService;

class BookletConfigService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/BookletConfigService';

    const OPTION_DEFAULT_VALUES = 'default_values';
    const OPTION_MENTION = 'mention';
    const OPTION_LINK = 'link';
    const OPTION_LOGO = 'logo';

    const CONFIG_REGULAR = 'regular';
    const CONFIG_LAYOUT = 'layout';
    const CONFIG_COVER_PAGE = 'cover_page';
    const CONFIG_PAGE_HEADER = 'page_header';
    const CONFIG_PAGE_FOOTER = 'page_footer';
    const CONFIG_ONE_PAGE_ITEM = 'one_page_item';
    const CONFIG_ONE_PAGE_SECTION = 'one_page_section';
    const CONFIG_BLANK_PAGES = 'add_blank_pages';
    const CONFIG_TITLE = 'title';
    const CONFIG_DESCRIPTION = 'description';
    const CONFIG_DATE = 'date';
    const CONFIG_LOGO = 'logo';
    const CONFIG_QRCODE = 'qr_code';
    const CONFIG_MENTION = 'mention';
    const CONFIG_LINK = 'link';
    const CONFIG_PAGE_NUMBER = 'page_number';

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

        BookletClassService::INSTANCE_COVER_PAGE_TITLE => self::CONFIG_TITLE,
        BookletClassService::INSTANCE_COVER_PAGE_DESCRIPTION => self::CONFIG_DESCRIPTION,
        BookletClassService::INSTANCE_COVER_PAGE_DATE => self::CONFIG_DATE,
        BookletClassService::INSTANCE_COVER_PAGE_LOGO => self::CONFIG_LOGO,
        BookletClassService::INSTANCE_COVER_PAGE_QRCODE => self::CONFIG_QRCODE,

        BookletClassService::INSTANCE_PAGE_LOGO => self::CONFIG_LOGO,
        BookletClassService::INSTANCE_PAGE_TITLE => self::CONFIG_TITLE,
        BookletClassService::INSTANCE_PAGE_MENTION => self::CONFIG_MENTION,
        BookletClassService::INSTANCE_PAGE_LINK => self::CONFIG_LINK,
        BookletClassService::INSTANCE_PAGE_DATE => self::CONFIG_DATE,
        BookletClassService::INSTANCE_PAGE_NUMBER => self::CONFIG_PAGE_NUMBER,
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

        $config = [
            self::CONFIG_LAYOUT => [],
            self::CONFIG_COVER_PAGE => [],
            self::CONFIG_PAGE_HEADER => [],
            self::CONFIG_PAGE_FOOTER => [],
            self::CONFIG_MENTION => $this->getOption(self::OPTION_MENTION),
            self::CONFIG_LINK => $this->getOption(self::OPTION_LINK),
            self::CONFIG_LOGO => $this->getOption(self::OPTION_LOGO),
            self::CONFIG_DATE => \tao_helpers_Date::displayeDate(time()),
            self::CONFIG_REGULAR => false,
        ];

        if (isset($properties[BookletClassService::PROPERTY_DESCRIPTION])) {
            $description = $properties[BookletClassService::PROPERTY_DESCRIPTION];
            if (is_array($description)) {
                $description = current($properties[BookletClassService::PROPERTY_DESCRIPTION]);
            }
            $config[self::CONFIG_DESCRIPTION] = (string)$description;
        }
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

        return $config;
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
