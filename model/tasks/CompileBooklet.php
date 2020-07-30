<?php

namespace oat\taoBooklet\model\tasks;

use common_exception_MissingParameter;
use common_Logger;
use common_report_Report as Report;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use core_kernel_persistence_Exception;
use Exception;
use JsonSerializable;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Task\CallbackTask;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletTaskService;
use oat\taoTests\models\MissingTestmodelException;
use tao_models_classes_dataBinding_GenerisFormDataBinder as GenerisFormDataBinder;
use tao_models_classes_dataBinding_GenerisFormDataBindingException;
use taoQtiTest_models_classes_QtiTestService;
use taoTests_models_classes_TestsService;

class CompileBooklet extends AbstractAction implements JsonSerializable, TaskAwareInterface
{
    use TaskAwareTrait;
    use OntologyAwareTrait;

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }

    /**
     * @param $params
     *
     * @return Report
     * @throws common_exception_MissingParameter
     * @throws core_kernel_persistence_Exception
     */
    public function __invoke($params)
    {
        if (!isset($params['test'])) {
            throw new common_exception_MissingParameter('Missing parameter `test` in ' . self::class);
        }

        $report = new Report(Report::TYPE_SUCCESS);
        $test = $this->getResource($params['test']);
        try {
            $model = taoTests_models_classes_TestsService::singleton()->getTestModel($test);
        } catch (MissingTestmodelException $e) {
            return Report::createFailure(__('Test Model does not exist for the test %s', $params['test']));
        }

        if ($model->getUri() !== taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            return Report::createFailure(__('%s is not a QTI test', $test->getLabel()));
        }

        // generate tao instance
        try {
            $instance = BookletClassService::singleton()->createBookletInstance(
                $this->getClass($params['bookletClass']),
                __('%s Booklet', $test->getLabel()),
                $test
            );
        } catch (Exception $e) {
            common_Logger::e($e->getMessage());
            return Report::createFailure(__('Error on the create booklet instance action'));
        }

        try {
            (new GenerisFormDataBinder($instance))->bind($params['initialProperties']);
        } catch (tao_models_classes_dataBinding_GenerisFormDataBindingException $e) {
            common_Logger::e($e->getMessage());
            return Report::createFailure(__('Error on the data binding'));
        }

        $this->getServiceLocator()->get(BookletTaskService::SERVICE_ID)->createPrintBookletTask($instance);

        // return report with instance
        $report->setMessage(__('Booklet %s created', $instance->getLabel()));
        $report->setData($instance);

        return $report;
    }

    /**
     * @param core_kernel_classes_Resource $test
     * @param core_kernel_classes_Class    $bookletClass
     * @param array                         $initialProperties
     *
     * @return CallbackTask|CallbackTaskInterface
     * @throws core_kernel_persistence_Exception
     */
    public static function createTask(
        core_kernel_classes_Resource $test,
        core_kernel_classes_Class $bookletClass,
        array $initialProperties = []
    ) {

        $action = new self();

        /** @var QueueDispatcher $queueDispatcher */
        $queueDispatcher = ServiceManager::getServiceManager()->get(QueueDispatcher::SERVICE_ID);

        $parameters = [
            'test' => $test->getUri(),
            'initialProperties' => $initialProperties
        ];

        if ($bookletClass !== null) {
            $parameters['bookletClass'] = $bookletClass->getUri();
        }

        return $queueDispatcher->createTask($action, $parameters, __('Creating "%s"', $test->getLabel()), null, true);
    }
}
