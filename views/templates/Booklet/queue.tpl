<div class="data-container-wrapper col-6">
    <div id="task-list"></div>
</div>

<script>
    require([
            'jquery',
            'util/url',
            'ui/taskQueue/table'
        ],
        function($, urlHelper, taskQueueTableFactory){
            var $queueArea = $('#task-list');
            taskQueueTableFactory({
                rows : 10,
                context : "<?=get_data('queueId')?>",
                dataUrl : urlHelper.route('getTasks', 'TaskQueueData', 'taoBooklet'),
                statusUrl : urlHelper.route('getStatus', 'TaskQueueData', 'taoBooklet'),
                removeUrl : urlHelper.route('archiveTask', 'TaskQueueData', 'taoBooklet'),
                downloadUrl : urlHelper.route('download', 'TaskQueueData', 'taoBooklet')
            })
                .init()
                .render($queueArea);
        });
</script>