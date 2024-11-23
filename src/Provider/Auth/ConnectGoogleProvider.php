<?php

namespace App\Provider\Auth;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Settings\Country;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConnectGoogleProvider implements ProviderInterface
{

    public function __construct(
        private readonly ClientRegistry        $clientRegistry,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string                $apiProjectDomain,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RedirectResponse
    {
        $userUid = $context['filters']['u_uid'] ?? 'no_user';
        $country = $context['filters']['country'] ?? 'no_country';
        if (!isset(Country::COUNTRIES[$country])) {
            $country = 'no_country';
        }
        $anonUid = $context['filters']['a_uid'] ?? 'no_anon';

        $redirectUri = sprintf('%s%s', $this->apiProjectDomain, $this->urlGenerator->generate('google_auth'));
        $state = sprintf('%s,%s,%s',
            $userUid,
            $country,
            $anonUid,
        );

        return $this->clientRegistry
            ->getClient('google')
            ->redirect(
                [
                    'email',
                ],
                [
                    'redirect_uri' => $redirectUri,
                    'state'        => $state,
                ],
            );
    }
}
