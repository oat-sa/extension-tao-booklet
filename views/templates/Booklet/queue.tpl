<div class="data-container-wrapper col-6">
    <div id="task-list"></div>
</div>

<script>
    require([
        'jquery',
        'taoBooklet/component/taskQueue'
    ],
    function($, taskQueueTableFactory){
        taskQueueTableFactory("<?=get_data('queueId')?>", $('#task-list'));
    });
</script>