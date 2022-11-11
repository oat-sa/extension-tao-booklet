<?php
use oat\tao\helpers\Layout;
use oat\tao\helpers\Template;
?><!doctype html>
<html>
<head>
    <?= Layout::getBundleLoader(Template::js('loader/taoBooklet.es5.min.js', 'taoBooklet'), 'taoBooklet/controller/PrintTest/render', get_data('client_params'), 'es5') ?>
</head>
<body>
</body>
</html>
