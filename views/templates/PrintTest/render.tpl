<?php
use oat\tao\helpers\Layout;
use oat\tao\helpers\Template;
?><!doctype html>
<html>
<head>
    <?= Layout::getAmdLoader(Template::js('loader/taoBooklet.min.js', 'taoBooklet'), 'taoBooklet/controller/PrintTest/render', get_data('client_params'), true) ?>
</head>
<body>
</body>
</html>
