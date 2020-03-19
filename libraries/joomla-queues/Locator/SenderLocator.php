<?php


namespace Weble\JoomlaQueues\Locator;


use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

class SenderLocator implements SendersLocatorInterface
{
    private $sendersMap = [];

    public function __construct(array $sendersMap)
    {
        $this->sendersMap = $sendersMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getSenders(Envelope $envelope): iterable
    {
        $seen = [];

        foreach (HandlersLocator::listTypes($envelope) as $type) {
            foreach ($this->sendersMap[$type] ?? [] as $senderAlias) {
                if (!\in_array($senderAlias, $seen, true)) {
                    if (!class_exists($senderAlias)) {
                        throw new RuntimeException(sprintf('Invalid senders configuration: sender "%s" is not in the senders locator.', $senderAlias));
                    }

                    $seen[] = $senderAlias;
                    $sender = $senderAlias;
                    yield $senderAlias => $sender;
                }
            }
        }
    }
}
