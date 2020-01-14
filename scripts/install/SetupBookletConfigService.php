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

namespace oat\taoBooklet\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\helpers\Template;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;

class SetupBookletConfigService extends InstallAction
{
    public function __invoke($params)
    {
        $bookletConfigService = new BookletConfigService([

            BookletConfigService::OPTION_DEFAULT_VALUES => [
                BookletClassService::PROPERTY_LAYOUT => [
                    BookletClassService::INSTANCE_LAYOUT_COVER,
                    BookletClassService::INSTANCE_LAYOUT_HEADER,
                    BookletClassService::INSTANCE_LAYOUT_FOOTER,
                ],

                BookletClassService::PROPERTY_COVER_PAGE => [
                    BookletClassService::INSTANCE_COVER_PAGE_TITLE,
                    BookletClassService::INSTANCE_COVER_PAGE_DESCRIPTION,
                    BookletClassService::INSTANCE_COVER_PAGE_DATE,
                    BookletClassService::INSTANCE_COVER_PAGE_LOGO,
                    BookletClassService::INSTANCE_COVER_PAGE_QRCODE,
                ],

                BookletClassService::PROPERTY_PAGE_HEADER => [
                    BookletClassService::INSTANCE_PAGE_LOGO,
                    BookletClassService::INSTANCE_PAGE_TITLE,
                    BookletClassService::INSTANCE_PAGE_DATE,
                ],

                BookletClassService::PROPERTY_PAGE_FOOTER => [
                    BookletClassService::INSTANCE_PAGE_MENTION,
                    BookletClassService::INSTANCE_PAGE_LINK,
                    BookletClassService::INSTANCE_PAGE_NUMBER,
                ],
            ],

            BookletConfigService::OPTION_MENTION => 'Built with TAO',

            BookletConfigService::OPTION_LINK => 'www.taotesting.com',

            BookletConfigService::OPTION_LOGO => Template::img('tao_logo_big.png', 'tao'),

        ]);

        $this->registerService(BookletConfigService::SERVICE_ID, $bookletConfigService);
    }
}
