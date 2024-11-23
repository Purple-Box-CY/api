<?php

namespace App\Enums\Auth;

enum RegistrationFailedReasonEnum: string
{
    case FAILED_TO_GET_USER = 'Failed to get user';
    case USER_DELETED = 'User is deleted';
}
