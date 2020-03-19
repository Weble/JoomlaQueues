<?php

use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\Console\Application;

defined('_JEXEC') or die;

class PlgConsoleQueue extends CMSPlugin
{
    protected $app;
    protected $autoloadLanguage = true;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        //JLoader::registerNamespace('Weble\\JoomlaCommands\\Commands\\', __DIR__ . '/commands', false, false, 'psr4');
    }

    public function onGetConsoleCommands(Application $console)
    {
        $console->addCommands([

        ]);
    }
}
