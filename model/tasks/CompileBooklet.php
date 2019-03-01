<?php
namespace oat\taoBooklet\model\tasks;


use common_report_Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\AbstractAction;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\taskQueue\QueueDispatcher;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletTaskService;
use oat\taoTests\models\MissingTestmodelException;
use tao_models_classes_dataBinding_GenerisFormDataBinder;

class CompileBooklet extends AbstractAction implements \JsonSerializable, TaskAwareInterface {

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
     * @return common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        if (!isset($params['test'])) {
            throw new \common_exception_MissingParameter('Missing parameter `test` in ' . self::class);
        }

        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS);
        $test = $this->getResource($params['test']);
        try {
            $model = \taoTests_models_classes_TestsService::singleton()->getTestModel($test);
        } catch (MissingTestmodelException $e) {
            return common_report_Report::createFailure(__('Test Model does not exist for the test %s', $params['test']));
        }

        if ($model->getUri() !== \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            return common_report_Report::createFailure(__('%s is not a QTI test', $test->getLabel()));
        }

        // generate tao instance
        $class  = $this->getClass($params['bookletClass']);
        try {
            $instance = BookletClassService::singleton()->createBookletInstance($class,
                __('%s Booklet', $test->getLabel()), $test);
        } catch (\Exception $e) {
            \common_Logger::e($e->getMessage());
            return common_report_Report::createFailure(__('Error on the create booklet instance action'));
        }
        $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($instance);
        try {
            $binder->bind($params['initialProperties']);
        } catch (\tao_models_classes_dataBinding_GenerisFormDataBindingException $e) {
            \common_Logger::e($e->getMessage());
            return common_report_Report::createFailure(__('Error on the data binding'));
        }

        $this->getServiceLocator()->get(BookletTaskService::SERVICE_ID)->createPrintBookletTask($instance);

        // return report with instance
        $report->setMessage(__('Booklet %s created', $instance->getLabel()));
        $report->setData($instance);
        return $report;
    }

    public static function createTask(\core_kernel_classes_Resource $test, \core_kernel_classes_Class $bookletClass, array $initialProperties = []) {
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
