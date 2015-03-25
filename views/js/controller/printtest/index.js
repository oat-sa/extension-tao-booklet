define([
    'jquery',
    'i18n',
    'helpers',
    'ui/feedback'
], function($, __, helpers, feedback){
    'use strict';


    var indexController = {
        start : function start(){

            var $container      = $('.print-test');

            var $renderingFrame = $('.test-content', $container);
            var uri             = $renderingFrame.data('uri');
            var renderingUrl    = helpers._url('render', 'PrintTest', 'taoBooklet', { uri : uri });

            var $printBtn       = $('.print', $container);
            var $genBtn         = $('.generate', $container);

            $renderingFrame
              .on('load', function frameLoaded(){

                $printBtn
                  .prop('disabled', false)
                  .off('click')
                  .on('click', function printTest(e){

                  });

                $genBtn
                  .prop('disabled', false)
                  .off('click')
                  .on('click', function generateDoc(e){

                  });
              })
              .on('error', function frameLoaded(err){
                feedback().error(__('The printed tests cannot be renderer : ') + err);
              })
              .attr('src', renderingUrl);
        }
    };

    return indexController;
});
