<section class="group-container"
         data-action-id='<?=get_data("id")?>'
         data-save-url='<?=get_data("saveUrl")?>'
         data-resource-uri='<?=get_data("resourceUri")?>'
         data-property-uri='<?=get_data("propertyUri")?>'
         data-open-nodes='<?=json_encode(get_data("openNodes"))?>'
         data-root-node='<?=get_data("rootNode")?>'
         data-uri='<?=get_data("resourceUri")?>'
         data-checked-nodes='<?=json_encode(tao_helpers_Uri::encodeArray(get_data("values")))?>'>
    <header>
        <h1><?=get_data('title')?></h1>
    </header>
    <div>
        <div id="<?=get_data('id')?>-tree"></div>
    </div>
    <footer>
        <button id="saver-action-<?=get_data('id')?>" class="btn-info small"
                type="button"><?=tao_helpers_Icon::iconSave().__('Save')?></button>
    </footer>

</section>
