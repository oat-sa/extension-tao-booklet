<?php
use oat\tao\helpers\Layout;
use oat\tao\helpers\Template;
?><!doctype html>
<html>
<head>
    <?= Layout::getAmdLoaderES5(Template::js('loader/taoBooklet.es5.min.js', 'taoBooklet'), 'taoBooklet/controller/PrintTest/render', get_data('client_params'), true) ?>
</head>
<body>
</body>
</html>
