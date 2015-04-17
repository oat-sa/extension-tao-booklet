define([
    'jquery',
    'taoBooklet/runner/testRunner',
    'json!taoBooklet/test/samples/test.json'
], function($, testRunner, testData){

    var container = 'outside-container';

    QUnit.module('Runner API');

    QUnit.test('module', function(assert){
        assert.ok(typeof testRunner !== 'undefined', "The module exports something");
        assert.ok(typeof testRunner === 'function', "The module exports a function");
    });


    QUnit.module('Render');

    QUnit.asyncTest('module', function(assert){
        QUnit.expect(1);
        testRunner($('.' + container), testData);
        setTimeout(function(){
            assert.ok(true);
            QUnit.start();
        }, 1000);
    });
});

