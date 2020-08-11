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

namespace oat\taoBooklet\form;

use common_Exception;
use Exception;
use oat\taoBooklet\model\BookletClassService;
use tao_actions_form_Instance;
use tao_helpers_form_elements_xhtml_Combobox as ComboBox;
use tao_helpers_form_Form;
use tao_helpers_form_FormFactory as FormFactory;
use tao_helpers_Uri;

/**
 * Create a form from a resource of your ontology.
 * Each property will be a field, regarding it's widget.
 *
 * @author Mikhail Kamarouski, <Komarouski@1pt.com>
 * @package oat\taoBooklet\form
 */
class EditForm extends tao_actions_form_Instance
{
    /**
     * Disable download button
     *
     * @param boolean $allowDownload
     *
     * @return EditForm
     */
    public function setAllowDownload(bool $allowDownload): self
    {
        if (!$allowDownload) {
            $this->getForm()
                ->getAction('Download')
                ->setAttribute('disabled', 'disabled');
        }

        return $this;
    }

    /**
     * @return mixed|void
     * @throws common_Exception
     * @throws Exception
     */
    protected function initElements()
    {
        parent::initElements();

        $form = $this->getForm();

        if ($form === null || !$form instanceof tao_helpers_form_Form) {
            return;
        }

        /** @var ComboBox $originalTestElement */
        $originalTestElement = $form->getElement(
            tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST)
        );
        $options = $originalTestElement->getOptions();

        $testElement = FormFactory::getElement('test', 'Readonly');
        if ($testElement) {
            $testElement->setDescription(__('Selected test'));
            $testElement->setValue($options[$originalTestElement->getRawValue()] ?? __('Test has been removed'));
        }

        $form->removeElement(tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST));
        $form->addElement($testElement);

        $downloadButton = FormFactory::getElement('Download', 'Button');
        if ($downloadButton) {
            $downloadButton->setValue(__('Download') . ' PDF');
            $downloadButton->setIcon('icon-download');
            $downloadButton->addClass('btn-download btn-success small');
        }

        $form->setActions(array_merge($this->form->getActions(), [$downloadButton]), 'bottom');
    }
}
