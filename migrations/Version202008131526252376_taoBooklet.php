<?php

declare(strict_types=1);

namespace oat\taoBooklet\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\taskQueue\TaskLogInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoBooklet\model\tasks\PrintBooklet;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202008131526252376_taoBooklet extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tagging tasks with categories';
    }

    public function up(Schema $schema): void
    {
        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

        $taskLogService->linkTaskToCategory(PrintBooklet::class, TaskLogInterface::CATEGORY_UPDATE);

        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLogService);
    }

    public function down(Schema $schema): void
    {
        /** @var TaskLogInterface $taskLogService */
        $taskLogService = $this->getServiceLocator()->get(TaskLogInterface::SERVICE_ID);

        $associations = $taskLogService->getOption(TaskLogInterface::OPTION_TASK_TO_CATEGORY_ASSOCIATIONS);

        unset($associations[PrintBooklet::class]);

        $taskLogService->setOption(TaskLogInterface::OPTION_TASK_TO_CATEGORY_ASSOCIATIONS, $associations);

        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLogService);
    }

}
