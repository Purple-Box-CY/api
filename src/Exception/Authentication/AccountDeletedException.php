<?php

namespace App\Exception\Authentication;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountDeletedException extends AccountStatusException
{
    public function getMessageKey(): string
    {
        return 'Account has deleted.';
    }
}
