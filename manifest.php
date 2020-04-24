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

use oat\taoBooklet\scripts\install\RegisterTestResultsPlugins;
use oat\taoBooklet\scripts\install\SetupBookletConfigService;
use oat\taoBooklet\scripts\install\SetupEventListeners;
use oat\taoBooklet\scripts\install\SetupStorage;

return [
    'name'        => 'taoBooklet',
    'label'       => 'Test Booklets',
    'description' => 'An extension for TAO to create test booklets (publishable in MS-Word and PDF along with Answer Sheets)',
    'license'     => 'GPL-2.0',
    'version'     => '3.4.2',
    'author'      => 'Open Assessment Technologies SA',
    'requires'    => [
        'generis' => '>=12.15.0',
        'tao'          => '>=30.0.0',
        'taoQtiTest'   => '>=29.0.0',
        'taoQtiPrint'  => '>=1.9.0',
        'taoOutcomeUi' => '>=7.0.0',
    ],
    // for compatibility
    'dependencies' => ['tao','taoQtiTest'],
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoBookletManager',
    'acl' => [
        ['grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoBookletManager', ['ext' => 'taoBooklet']],
        ['grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', ['ext' => 'taoBooklet', 'mod' => 'PrintTest', 'act' => 'render']],
    ],
    'models' => [
       'http://www.tao.lu/Ontologies/Booklet.rdf#Booklet'
    ],
    'install' => [
        'php' => [
            SetupStorage::class,
            SetupBookletConfigService::class,
            RegisterTestResultsPlugins::class,
            SetupEventListeners::class,
        ],
        'rdf' => [
            __DIR__ . '/scripts/install/booklet.rdf',
        ],
        'checks' => [
        ]
    ],
    'uninstall' => [
    ],
    'update' => 'oat\\taoBooklet\\scripts\\update\\Updater',
    'routes' => [
        '/taoBooklet' => 'oat\\taoBooklet\\controller'
    ],
    'constants' => [
        # views directory
        "DIR_VIEWS" => __DIR__ . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL . 'taoBooklet/',
    ],
    'extra' => [
        'structures' => __DIR__ . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml',
    ]
];
