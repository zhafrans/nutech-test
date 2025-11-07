<?php

namespace App\Enums;

enum ResponseCode: string
{
    case Ok                         = '20000';
    case TooManyAttempts            = '40001';
    case LoginFailed                = '40002';
    case MissingMandatoryField      = '40003';
    case InvalidUser                = '40004';
    case InvalidFieldFormat         = '40005';
    case Unauthorized               = '40100';
    case Forbidden                  = '40300';
    case ValidationError            = '42200';
    case GeneralError               = '50000';

    public function getMessage(): string
    {
        return match ($this) {
            self::Ok => 'OK',
            self::LoginFailed => 'The provided credentials do not match our records',
            self::MissingMandatoryField => 'Missing mandatory field',
            self::InvalidFieldFormat => 'Invalid field format',
            self::InvalidUser => 'Invalid user',
            self::ValidationError => 'Validation error',
            self::GeneralError => 'General error',
            self::Unauthorized => 'Token tidak tidak valid atau kadaluwarsa',
        };
    }

    public function getFullCode(): string
    {
        return $this->getStatusCode() . request()->attributes->get('service_code', '00') . $this->getCaseCode();
    }

    public function getStatusCode(): int
    {
        return (int) substr($this->value, 0, 3);
    }

    public function getCaseCode(): string
    {
        return substr($this->value, 3);
    }
}
