<?php


namespace Weble\JoomlaQueues\Admin\View\CPanel;


class Html extends \FOF30\View\DataView\Html
{
    protected function preRender()
    {
        $view = $this->getName();
        $task = $this->task;

        // Don't load the toolbar on CLI
        $platform = $this->container->platform;

        if (!$platform->isCli())
        {
            $toolbar = $this->container->toolbar;
            $toolbar->renderToolbar($view, $task);
        }

        parent::preRender(); // TODO: Change the autogenerated stub
    }
}
