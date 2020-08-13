<?php
use oat\tao\helpers\Template;
Template::inc('form_context.tpl', 'tao');
?>
<?= tao_helpers_Scriptloader::render() ?>
<header class="section-header flex-container-full">
    <h2><?=get_data('form-title')?></h2>
</header>
<div class="main-container flex-container-main-form">
    <div class="form-content">
        <?=get_data('form-fields')?>
    </div>
</div>

<?php Template::inc('footer.tpl', 'tao'); ?>

<script>
    requirejs.config({
        config: {
            'taoBooklet/controller/Booklet/editBooklet' : <?= get_data('module-config') ?>
        }
    })
</script>
