<?php
use oat\tao\helpers\Template;
use oat\tao\model\layout\AmdLoader;

$configUrl    = get_data('client_config_url');
$requireJsUrl = Template::js('lib/require.js', 'tao');
$bootstrapUrl = Template::js('loader/bootstrap.js', 'tao');

$loader = new AmdLoader($configUrl, $requireJsUrl, $bootstrapUrl);
?><!doctype html>
<html>
<head>
    <?= $loader->getBundleLoader(Template::js('loader/taoBookletRunner.min.js', 'taoBooklet'), 'taoBooklet/controller/PrintTest/render', get_data('client_params')); ?>
</head>
<body>
</body>
</html>
