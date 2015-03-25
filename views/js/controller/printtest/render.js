define([
    'jquery',
], function($){
    'use strict';


    var renderController = {
        start : function start(testData){

           $('body').empty().html('<pre>' + JSON.stringify(testData, null, 2) + '</pre>');

        }
    };

    return renderController;
});
