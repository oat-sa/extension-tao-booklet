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
namespace oat\taoBooklet\form;

/**
 * Create a form from a resource of your ontology.
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @author Mikhail Kamarouski, <Komarouski@1pt.com>
 * @package taoBooklet
 */
namespace oat\taoBooklet\form;

use oat\taoBooklet\model\BookletClassService;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

class EditForm extends \tao_actions_form_Instance
{

    /**
     * Disable download button
     * @param boolean $allowDownload
     */
    public function setAllowDownload( $allowDownload )
    {
        $downloadBtn = $this->getForm()->getAction('Download');
        if ( ! $allowDownload ) {
            $downloadBtn->setAttribute( 'disabled', 'disabled' );
        }
    }


    protected function initElements()
    {
        parent::initElements();

        /** @var \tao_helpers_form_elements_xhtml_Combobox $originalTestElement */
        $originalTestElement = $this->getForm()->getElement( tao_helpers_Uri::encode( BookletClassService::TEST_URI ) );
        $options             = $originalTestElement->getOptions();

        $formatElt = tao_helpers_form_FormFactory::getElement( 'test', 'Readonly' );
        $formatElt->setDescription( __( 'Selected test' ) );
        $formatElt->setValue( isset($options[$originalTestElement->getRawValue()])?$options[$originalTestElement->getRawValue()]:__('Test has been removed') );

        $downloadBtn = \tao_helpers_form_FormFactory::getElement( 'Download', 'Button' );
        $downloadBtn->setValue( __( 'Download' ) . ' PDF' );
        $downloadBtn->setIcon( 'icon-download' );
        $downloadBtn->addClass( 'btn-download btn-success small' );

        $this->getForm()->removeElement( tao_helpers_Uri::encode( BookletClassService::TEST_URI ) );
        $this->getForm()->setActions( array_merge( $this->form->getActions(), array( $downloadBtn ) ), 'bottom' );
        $this->getForm()->addElement( $formatElt );

    }

}