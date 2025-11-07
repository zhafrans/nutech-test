<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case TOPUP = '01';
    case PAYMENT = '02';

    public function getName(): string
    {
        return $this->name;
    }
}
