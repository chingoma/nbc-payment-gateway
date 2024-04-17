<?php

namespace Lockminds\CrdbPaymentGateway\Rules;


use Lockminds\CrdbPaymentGateway\Rules\TransactionAvailableValidation;

class CrdbValidationHelper
{
    public static function accountToWalletRequestValidator(): array
    {
        return [
            'customer_msisdn' => self::msisdnValidator(),
            'amount' => ['required'],
            'sender_name' => ['required', 'string'],
            'language1' => ['required', 'string'],
            'id' => ['required'],
        ];
    }

    public static function createRequestValidator(): array
    {
        return [
            'customer_msisdn' => self::msisdnValidator(),
            'amount' => ['required'],
            'remarks' => ['required'],
            'id' => ['required'],
        ];
    }

    public static function refundTransactionValidator(): array
    {
        return [
            'customer_msisdn' => self::msisdnValidator(),
            'amount' => ['required'],
            'reference_id' => self::referenceIdValidator(),
            'transaction_id' => self::transactionIdValidator(),
            'id' => ['required'],
        ];
    }

    public static function msisdnValidator(): array
    {
        return ['required', 'string', 'phone:TZ'];
    }

    public static function referenceIdValidator(): array
    {
        return ['required', 'string', new ReferenceAvailableValidation];
    }

    public static function transactionIdValidator(): array
    {
        return ['required', 'string', new TransactionAvailableValidation];
    }
}
