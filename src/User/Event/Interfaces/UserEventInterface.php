<?php

namespace App\User\Event\Interfaces;

use App\User\Entity\User;

interface UserEventInterface
{
    public function getUser(): User;
}