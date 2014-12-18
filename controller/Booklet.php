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
namespace oat\taoBooklet\controller;

use tao_actions_SaSModule;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletGenerator;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use oat\taoBooklet\form\WizardForm;

/**
 * Controller to managed assembled deliveries
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @package taoDelivery
 */
class Booklet extends tao_actions_SaSModule
{
    public function __construct()
    {
        $this->service = BookletClassService::singleton();
    }
    
    /**
     * (non-PHPdoc)
     * @see tao_actions_SaSModule::getClassService()
     */
    protected function getClassService()
    {
        return BookletClassService::singleton();
    }
    
    /**
     * Main action
     *
     * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
     * @return void
     */
    public function index()
    {
        $this->setView('index.tpl');
    }
    
    public function wizard()
    {
        $this->defaultData();
        try {
            $formContainer = new WizardForm(array('class' => $this->getCurrentClass()));
            $myForm = $formContainer->getForm();

            if ($myForm->isValid() && $myForm->isSubmited()) {
                
                $test = new core_kernel_classes_Resource($myForm->getValue('test'));
                $bookletClass = new core_kernel_classes_Class($myForm->getValue('classUri'));
                
                $report = BookletGenerator::generate($test, $bookletClass);
                $this->returnReport($report);
                
            } else {
                $this->setData('myForm', $myForm->render());
                $this->setData('formTitle', __('Create a new booklet'));
                $this->setView('form.tpl', 'tao');
            }
        
        } catch (taoSimpleDelivery_actions_form_NoTestsException $e) {
            $this->setView('Booklet/wizard.tpl');
        }
    }

}
