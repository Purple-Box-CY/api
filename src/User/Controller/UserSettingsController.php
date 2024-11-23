<?php

namespace App\User\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\User\Provider\Settings\SettingsNotificationsUnsubscribeProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[ApiResource(
    shortName: 'User Info',
    operations: [
        new Get(
            uriTemplate: '/user/settings/notifications/unsubscribe',
            openapi: new Operation(
                summary: 'Unsubscribe mail notifications',
                description: 'Unsubscribe mail notifications',
            ),
            output: RedirectResponse::class,
            name: 'unsubscribe',
            provider: SettingsNotificationsUnsubscribeProvider::class,
        ),
    ],
)]
class UserSettingsController extends AbstractController
{
}

