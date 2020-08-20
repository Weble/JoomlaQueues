<?php


namespace Weble\JoomlaQueues\Transport;

use Joomla\Registry\Registry;
use Symfony\Component\Messenger\Transport\RedisExt\Connection;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Transport\RedisExt\RedisTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class RedisTransportProvider extends TransportProvider
{
    /** @var Registry|null */
    protected $params;

    public function __construct(?Registry $params = null)
    {
        $this->params = $params ?: new Registry([]);
    }

    public function getName(): string
    {
        return 'Redis';
    }

    public function transport(): TransportInterface
    {
        $auth = [
            'host' => $this->params->get('host', '127.0.0.1'),
            'port' => $this->params->get('port', '6379')
        ];

        if ($password = $this->params->get('password')) {
            $auth['auth'] = $password;
        }

        return new RedisTransport(new Connection([
            'dbindex' => $this->params->get('dbindex', 1),
            'stream' => $this->params->get('stream', 'joomla-queues-messages'),
            'group' => $this->params->get('group', 'joomla-queues'),
            'consumer' => $this->params->get('consumer', 'consumer'),
            'auto_setup' => $this->params->get('auto_setup', true),
        ], $auth), $this->serializer());
    }

    public function retryStrategy(): RetryStrategyInterface
    {
        if (!$this->params) {
            return parent::retryStrategy();
        }

        if (!$this->params->get('override_retry_strategy', 0)) {
            return parent::retryStrategy();
        }

        return $this->useMultiplierRetryStrategy($this->params);
    }
}
