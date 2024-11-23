<?php

namespace App\Provider\Event;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Infrastructure\SSE\Event;
use App\Service\Infrastructure\SSE\EventType;
use App\Service\Infrastructure\SSE\SSE;
use App\Service\Infrastructure\SSE\StopSSEException;
use App\User\Service\UserAuthService;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventProvider implements ProviderInterface
{
    public function __construct(
        private UserService                 $userService,
        private UserAuthService             $userAuthService,
        private RedisService                $redisService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): StreamedResponse
    {
        $started = false;
        $checkCount = 0;
        try {
            $currentUser = $this->userService->getCurrentUser();
            $this->userService->checkUser($currentUser);
        } catch (\Exception $e) {
            $currentUser = null;
        }

        if (!$currentUser && $authToken = $context['filters']['auth'] ?? null) {
            try {
                $currentUser = $this->userAuthService->getUserByToken($authToken);
            } catch (\Exception $e) {
                $currentUser = null;
            }
        }

        $eventCallback = function () use ($currentUser, &$started, &$checkCount) {
            $callbackUpdateConfig = function () use ($currentUser, &$started, &$checkCount) {
                $event = null;
                $data = null;

                if (!$started) {
                    $started = true;

                    return [
                        'event' => EventType::CONNECTED,
                        'data'  => [
                            'startTime' => time(),
                        ],
                    ];
                }

                if ($configUpdated = $this->redisService->get(RedisKeys::KEY_CONFIG_UPDATE, false)) {
                    return [
                        'event' => EventType::CONFIG_UPDATED,
                        'data'  => json_decode($configUpdated, true),
                    ];
                }

                $shouldStop = $checkCount++ > 7; // Stop if something happens or to clear connection, browser will retry
                if ($shouldStop) {
                    throw new StopSSEException();
                }

                if (empty($data)) {
                    return false; // Return false if no new messages
                }

                return [
                    'event' => $event,
                    'data'  => $data,
                ];

                //return json_encode(compact('data', 'event'));
                // return ['event' => 'ping', 'data' => 'ping data']; // Custom event temporarily: send ping event
                // return ['id' => uniqid(), 'data' => json_encode(compact('news'))]; // Custom event Id
            };


            $updateConfigSSE = new SSE(new Event($callbackUpdateConfig, 'event'));
            $updateConfigSSE->start();
        };

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering',
            'no'); // Nginx: unbuffered responses suitable for Comet and HTTP streaming applications
        $response->setCallback($eventCallback);

        return $response;
    }
}
