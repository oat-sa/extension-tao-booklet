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

/**
 * Class BookletDataService
 * @package oat\taoBooklet\model
 */
class BookletDataService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/BookletDataService';
    const CACHE_PREFIX = 'booklet_data_';

    /**
     * @var \common_cache_Cache
     */
    protected $cache;

    /**
     * @return \common_cache_Cache
     */
    protected function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = $this->getServiceManager()->get('generis/cache');
        }
        return $this->cache;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getCacheKey($key)
    {
        return self::CACHE_PREFIX . $key;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getData($key)
    {
        $cache = $this->getCache();
        $entry = $this->getCacheKey($key);

        if ($cache->has($entry)) {
            return json_decode($cache->get($entry), true);
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
        $this->getCache()->put(json_encode($data), $this->getCacheKey($key));
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function cleanData($key)
    {
        $cache = $this->getCache();
        $entry = $this->getCacheKey($key);

        if ($cache->has($entry)) {
            $cache->remove($entry);
        }
        return $this;
    }
}