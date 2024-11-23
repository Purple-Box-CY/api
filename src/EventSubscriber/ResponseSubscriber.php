<?php

namespace App\EventSubscriber;

use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\RequestService;
use App\Service\Utility\MomentHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class ResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestService  $requestService,
        private RedisService    $redisService,
        private LogService      $logger,
        private KernelInterface $kernel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ResponseEvent::class => 'onKernelResponse'];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $userId = $response->headers->get(RequestService::HEADER_USER_ID);
        if (!$userId) {
            $userId = $event->getRequest()->headers->get(RequestService::HEADER_USER_ID);
        }
        if (!$userId) {
            $userId = $this->requestService->generateUserId();
        }

        $response->headers->set(RequestService::HEADER_USER_ID, $userId);

        $redisKey = sprintf(RedisKeys::KEY_USER_COUNTRY, md5($userId));
        $country = $this->redisService->get($redisKey, false);
        if (!$country) {
            $ip = $this->getIP();
            if ($this->kernel->getEnvironment() == 'dev') {
                $ip = '62.228.174.207';
            }

            // remember chmod 0777 for folder 'cache'
            $cacheIpDir = sprintf('%s/../ip', $this->kernel->getCacheDir());
            $file = sprintf('%s/%s', $cacheIpDir, $ip);
            if (!file_exists($file)) {
                // request
                $json = file_get_contents('http://www.geoplugin.net/json.gp?ip='.$ip);
                $data = json_decode($json);
                try {
                    $country = $data->geoplugin_countryCode;
                } catch (\Exception $e) {
                    $this->logger->error('Failed to get Country by IP',
                        [
                            'ip'    => $ip,
                            'data'  => $data,
                            'error' => $e->getMessage(),
                        ]);
                    $country = 'XX';
                }

                if (!file_exists($cacheIpDir)) {
                    mkdir($cacheIpDir, 0777, true);
                }
                try {
                    $f = fopen($file, "w+");
                    fwrite($f, $country);
                    fclose($f);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to write '.$file,
                        [
                            'error' => $e->getMessage(),
                        ]);
                }
            } else {
                $country = file_get_contents($file);
            }

            if (!$country) {
                $country = 'XX';
            }

            $this->redisService->set($redisKey, $country, MomentHelper::SECONDS_HOUR, false);
        }

        $response->headers->set('country', $country);
    }

    private function getIP(): ?string
    {
        $server_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($server_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return null;
    }
}