<?php

namespace App\Domain\User;

enum UserType: string
{
    case COMMON = 'common';
    case MERCHANT = 'merchant';
}
