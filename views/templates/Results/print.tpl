<?php
use oat\tao\helpers\Template;
Template::inc('form_context.tpl', 'tao');
?>
<link rel="stylesheet" type="text/css" href="<?= Template::css('report.css','tao') ?>" media="screen"/>
<?= tao_helpers_Scriptloader::render() ?>
<header class="section-header flex-container-full"
        data-select-node="<?= get_data('selectNode'); ?>"
        data-queue="<?= get_data('queueId'); ?>"
>
    <h2><?=get_data('formTitle')?></h2>
</header>
<div class="print-form main-container flex-container-main-form" data-purpose="form">
    <div class="form-content">
        <?=get_data('myForm')?>
    </div>
</div>
<div class="print-report main-container flex-container-full report hidden" data-purpose="report">
    <label id="fold">
        <span class="check-txt"><?php echo __("Show detailed report"); ?></span>
        <input type="checkbox"/>
        <span class="icon-checkbox"></span>
    </label>
</div>

<?php Template::inc('footer.tpl', 'tao'); ?>
