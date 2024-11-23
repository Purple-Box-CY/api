<?php

namespace App\Service\Infrastructure\SSE;

class SSE
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Start SSE Server
     * @param int $interval in seconds
     */
    public function start($interval = 5)
    {
        while (true) {
            try {
                $result = $this->event->fill();
                if ($result->getData()) {
                    echo $result;
                } else {
                    sleep($interval);
                    continue;
                }
            } catch (StopSSEException $e) {
                return;
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();

            // if the connection has been closed by the client we better exit the loop
            if (connection_aborted()) {
                return;
            }
            sleep($interval);
        }
    }
}
