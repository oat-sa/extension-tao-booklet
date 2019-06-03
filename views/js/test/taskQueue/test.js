define([
    'jquery',
    'util/url',
    'taoBooklet/component/taskQueue'
], function($, urlHelper, taskQueueFactory) {
    QUnit.module('Runner API');

    QUnit.test('module', function(assert) {
        assert.ok(typeof taskQueueFactory !== 'undefined', 'The module exports something');
        assert.ok(typeof taskQueueFactory === 'function', 'The module exports a function');
    });
});

