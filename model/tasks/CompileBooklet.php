<?php

namespace oat\taoBooklet\model\tasks;

use common_exception_Error;
use common_exception_MissingParameter;
use common_Logger;
use common_report_Report as Report;
use core_kernel_classes_Class as KernelClass;
use core_kernel_classes_Resource as KernelResource;
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

    private const PARAM_TEST = 'test';
    private const PARAM_CLASS = 'class';
    private const PARAM_PROPS = 'properties';

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
     * @throws common_exception_Error
     */
    public function __invoke($params = []): Report
    {
        $this->validateParameters(
            $params,
            self::PARAM_CLASS, self::PARAM_TEST
        );

        $class = $this->getClass($params[self::PARAM_CLASS]);
        $test = $this->getResource($params[self::PARAM_TEST]);

        $validationReport = $this->validateTest($test);

        if ($validationReport->containsError()) {
            return $validationReport;
        }

        try {
            /** @var KernelResource $booklet */
            $booklet = BookletClassService::singleton()->createBookletInstance(
                $class, '', $test
            );
        } catch (Exception $e) {
            common_Logger::e($e->getMessage());
            return Report::createFailure(__('Error on the create booklet instance action'));
        }

        try {
            (new GenerisFormDataBinder($booklet))->bind($params[self::PARAM_PROPS]);
        } catch (tao_models_classes_dataBinding_GenerisFormDataBindingException $e) {
            common_Logger::e($e->getMessage());
            return Report::createFailure(__('Error on the data binding'));
        }

        // set custom label while task in running
        $booklet->setLabel('in progress');

        /** @var BookletTaskService $taskService */
        $taskService = $this->getServiceLocator()->get(BookletTaskService::SERVICE_ID);

        $taskService->createPrintBookletTask($booklet);

        return Report::createSuccess(__('Booklet for test %s created', $test->getLabel()), $booklet);
    }

    /**
     * @param        $params
     * @param string ...$keys
     *
     * @throws common_exception_MissingParameter
     */
    private function validateParameters($params, string ...$keys)
    {
        foreach ($keys as $key) {
            if (!isset($params[$key])) {
                throw new common_exception_MissingParameter(
                    sprintf('`%s` in %s', $key, self::class)
                );
            }
        }
    }

    /**
     * @param KernelResource $test
     *
     * @return Report
     * @throws core_kernel_persistence_Exception
     */
    private function validateTest(KernelResource $test): Report
    {
        try {
            $model = taoTests_models_classes_TestsService::singleton()->getTestModel($test);
        } catch (MissingTestmodelException $e) {
            return Report::createFailure(__('Test Model does not exist for the test %s', $test->getLabel()));
        }

        if ($model->getUri() !== taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            return Report::createFailure(__('%s is not a QTI test', $test->getLabel()));
        }

        return Report::createSuccess('Test successfully validated');
    }

    /**
     * @param KernelClass    $class
     * @param KernelResource $test
     * @param array          $properties
     *
     * @return CallbackTask|CallbackTaskInterface
     * @throws core_kernel_persistence_Exception
     */
    public static function createTask(KernelClass $class, KernelResource $test, array $properties = []): CallbackTaskInterface
    {
        /** @var QueueDispatcher $queueDispatcher */
        $queueDispatcher = ServiceManager::getServiceManager()->get(QueueDispatcher::SERVICE_ID);

        return $queueDispatcher->createTask(
            new self(),
            [
                self::PARAM_CLASS => $class->getUri(),
                self::PARAM_TEST => $test->getUri(),
                self::PARAM_PROPS => $properties
            ],
            __('Creating booklet instance for test "%s"', $test->getLabel()),
            null, true
        );
    }
}
