<?php
defined('_JEXEC') or die();

/** @var \Weble\JoomlaQueues\Admin\Model\Jobs $model */
$model = $this->getModel();
?>


<div id="j-sidebar-container" class="span2">
    <?php echo JHtmlSidebar::render();?>
</div>
<div id="j-main-container" class="span10">
    <div class="well">
        <ul class="divider unstyled">
            <li>{{ $model->tmpInstance()->job_status('waiting')->count() }} waiting jobs</li>
            <li>{{ $model->tmpInstance()->job_status('processing')->count() }} processing jobs</li>
            <li>{{ $model->tmpInstance()->job_status('handled')->count() }} handled jobs</li>
            <li>{{ $model->tmpInstance()->job_status('failed')->count() }} failed jobs</li>
        </ul>
    </div>
</div>
