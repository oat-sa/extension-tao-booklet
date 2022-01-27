<?php

declare(strict_types=1);

namespace oat\taoBooklet\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\SearchProxy;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoBooklet\model\BookletClassService;

final class Version202101271514252376_taoBooklet extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add taoBooklet to OPTION_GENERIS_SEARCH_WHITELIST';
    }

    public function up(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $searchProxy->extendGenerisSearchWhiteList(
            [BookletClassService::CLASS_URI]
        );

        $this->registerService(SearchProxy::SERVICE_ID, $searchProxy);
    }

    public function down(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $searchProxy->removeFromGenerisSearchWhiteList(
            [BookletClassService::CLASS_URI]
        );

        $this->registerService(SearchProxy::SERVICE_ID, $searchProxy);
    }
}
