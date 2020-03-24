<?php


namespace Weble\JoomlaQueues\Admin\View\Jobs;


class Html extends \FOF30\View\DataView\Html
{
    protected function onBeforeBrowse()
    {
        parent::onBeforeBrowse(); // TODO: Change the autogenerated stub

        \JHtmlSidebar::addFilter('Job Status', 'job_status', \JHtmlSelect::options([
            'failed' => \JText::_('COM_QUEUES_JOBS_FAILED'),
            'processing' => \JText::_('COM_QUEUES_JOBS_RECEIVED'),
            'handled' => \JText::_('COM_QUEUES_JOBS_HANDLED'),
            'waiting' => \JText::_('COM_QUEUES_JOBS_WAITING'),
        ], 'value', 'text', $this->getModel()->getState('job_status')));
    }
}
