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
 * Copyright (c) 2017-2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBooklet\form;

use tao_actions_form_Instance;
use tao_helpers_form_FormFactory as FormFactory;

/**
 * Create a form from a booklet
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @package taoBooklet
 */
class GenerateForm extends tao_actions_form_Instance
{
    public function initElements()
    {
        parent::initElements();

        $createButton = FormFactory::getElement('create', 'Button');
        if ($createButton) {
            $createButton->setValue(__('Generate'));
            $createButton->setIcon("icon-play");
            $createButton->addClass("form-submitter btn-success small");
        }

        $this->getForm()->setActions([$createButton], 'bottom');
    }
}
