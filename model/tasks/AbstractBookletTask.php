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

namespace oat\taoBooklet\model\tasks;

use common_session_DefaultSession;
use common_session_SessionManager;
use core_kernel_classes_Resource;
use core_kernel_users_GenerisUser;
use JsonSerializable;
use oat\oatbox\task\AbstractTaskAction;
use PHPSession;

/**
 * Class AbstractBookletTask
 * @package oat\taoBooklet\model\tasks
 */
abstract class AbstractBookletTask extends AbstractTaskAction implements JsonSerializable
{
    /**
     * Create a session for a particular user in CLI
     * @param string $userUri
     */
    protected function startCliSession($userUri)
    {
        $user = new core_kernel_users_GenerisUser(new core_kernel_classes_Resource($userUri));
        $session = new common_session_DefaultSession($user);

        // force a session, cannot use the SessionManager as it does not allow session in CLI
        // the session is required by the PrintTest controller called to render the PDF
        session_name(GENERIS_SESSION_NAME);
        session_start();
        PHPSession::singleton()->setAttribute(common_session_SessionManager::PHPSESSION_SESSION_KEY, $session);

        common_session_SessionManager::startSession($session);
    }
}