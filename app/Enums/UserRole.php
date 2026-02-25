<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'User';
    case VERIFIER = 'Verifier';
    case APPROVER = 'Approver';
}