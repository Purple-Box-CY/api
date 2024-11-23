<?php

declare(strict_types=1);

namespace App\Security\Infrastructure;

use App\User\Entity\Interfaces\AppUserInterface;
use Webmozart\Assert\Assert;
use App\Security\Domain\AuthUserInterface;
use App\Security\Domain\UserFetcherInterface;
use Symfony\Component\Security\Core\Security;

class UserFetcher implements UserFetcherInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getAuthUser(): AuthUserInterface
    {
        /** @var AuthUserInterface $user */
        $user = $this->security->getUser();

        Assert::notNull($user, 'Current user not found check security access list');
        Assert::isInstanceOf($user, AuthUserInterface::class, sprintf('Invalid user type %s', \get_class($user)));

        return $user;
    }

    public function getCurrentUser(): ?AppUserInterface
    {
        /** @var AppUserInterface $user */
        $user = $this->security->getUser();

        return $user;
    }

}
