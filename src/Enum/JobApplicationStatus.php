<?php

namespace App\Enum;

enum JobApplicationStatus: string
{
    case WISHLIST = 'wishlist';
    case APPLIED = 'applied';
    case INTERVIEW = 'interview';
    case REJECTED = 'rejected';
    case ACCEPTED = 'accepted';
    case OFFERED = 'offered';
}
