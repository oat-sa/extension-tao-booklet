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

namespace oat\taoBooklet\model;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\service\StateStorage;

/**
 * Class BookletDataService
 * @package oat\taoBooklet\model
 */
class BookletDataService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/BookletDataService';
    const STORAGE_PREFIX = 'booklet_data_';
    const STORAGE_USER = 'BookletUser';

    /**
     * @var StateStorage
     */
    protected $storage;

    /**
     * @return StateStorage
     */
    protected function getStorage()
    {
        if (!isset($this->storage)) {
            $this->storage = $this->getServiceManager()->get('tao/stateStorage');
        }
        return $this->storage;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getStorageKey($key)
    {
        return self::STORAGE_PREFIX . $key;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getData($key)
    {
        $storage = $this->getStorage();
        $entry = $this->getStorageKey($key);

        if ($storage->has(self::STORAGE_USER, $entry)) {
            return json_decode($storage->get(self::STORAGE_USER, $entry), true);
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    public function setData($key, $data)
    {
        $this->getStorage()->set(self::STORAGE_USER, $this->getStorageKey($key), json_encode($data));
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function cleanData($key)
    {
        $storage = $this->getStorage();
        $entry = $this->getStorageKey($key);

        if ($storage->has(self::STORAGE_USER, $entry)) {
            $storage->del(self::STORAGE_USER, $entry);
        }
        return $this;
    }
}