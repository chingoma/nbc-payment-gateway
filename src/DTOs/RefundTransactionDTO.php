<?php

namespace Lockminds\CrdbPaymentGateway\DTOs;

use Lockminds\CrdbPaymentGateway\Rules\CrdbValidationHelper;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class RefundTransactionDTO extends ValidatedDTO
{
    public string $id;

    public string $customer_msisdn;

    public string|float $amount;

    public string $transaction_id;

    public string $reference_id;

    protected function defaults(): array
    {
        return [];
    }

    /**
     * @return array[]
     */
    protected function rules(): array
    {
        return CrdbValidationHelper::refundTransactionValidator();
    }

    /**
     * @return array[]
     */
    public function messages(): array
    {
        return [
            'phone' => 'The given mobile is not a valid mobile number.',
        ];
    }

    /**
     * @return StringCast[]
     */
    protected function casts(): array
    {
        return [];
    }
}
