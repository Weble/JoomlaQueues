<?php


namespace Weble\JoomlaQueues\Admin\Model;


use FOF30\Container\Container;
use FOF30\Model\DataModel;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class Jobs extends DataModel
{
    public function __construct(Container $container, array $config = array())
    {
        $config['idFieldName'] = 'id';

        parent::__construct($container, $config);
    }

    public function message(): Envelope
    {
        return (new PhpSerializer())->decode($this->toArray());
    }
}
