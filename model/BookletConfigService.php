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

use oat\oatbox\service\ConfigurableService;

class BookletConfigService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/BookletConfigService';

    const OPTION_DEFAULT_VALUES = 'default_values';
    const OPTION_MENTION = 'mention';
    const OPTION_LINK = 'link';

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
}