<?php
use oat\tao\helpers\Template;
Template::inc('form_context.tpl', 'tao');
?>
<?= tao_helpers_Scriptloader::render() ?>
<header class="section-header flex-container-full">
    <h2><?=__('Preview')?></h2>
</header>
<div class="main-container flex-container-main-form">
    <?=get_data('instance')->getLabel()?>
</div>

<?php Template::inc('footer.tpl', 'tao'); ?>
