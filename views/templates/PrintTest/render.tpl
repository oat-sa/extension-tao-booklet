<?php
use oat\tao\helpers\Template;
?><!doctype html>
<html>
<head>
    <script src="<?= Template::js('lib/require.js', 'tao')?>"></script>
    <script>
        (function(){
            var clientConfigUrl = <?= json_encode(get_data('client_config_url')) ?> ;
            var testData     =  <?= get_data('testData') ?>;
            require([clientConfigUrl], function(){
                require(['taoBooklet/controller/printtest/render'], function(controller){
                    controller.start(testData);
                });
            });
        }());
    </script>
</head>
<body>
</body>
</html>
