<?php

namespace App\Enums;

enum MovementType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
}
