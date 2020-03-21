<?php

namespace Weble\JoomlaQueues\Admin\Toolbar;

use Joomla\CMS\Toolbar\ToolbarHelper;

class Toolbar extends \FOF30\Toolbar\Toolbar
{
    public function onBrowse()
    {
        // On frontend, buttons must be added specifically
        if ($this->container->platform->isBackend() || $this->renderFrontendSubmenu) {
            $this->renderSubmenu();
        }

        if (!$this->container->platform->isBackend() && !$this->renderFrontendButtons) {
            return;
        }

        // Setup
        $option = $this->container->componentName;
        $view = $this->container->input->getCmd('view', 'cpanel');

        // Set toolbar title
        $subtitle_key = strtoupper($option . '_TITLE_' . $view);
        \JToolBarHelper::title(\JText::_(strtoupper($option)) . ': ' . \JText::_($subtitle_key), str_replace('com_', '', $option));

        ToolbarHelper::preferences('com_queues', '500', '660');
    }
}
