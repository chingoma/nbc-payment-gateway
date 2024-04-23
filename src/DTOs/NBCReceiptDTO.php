<?php

namespace Lockminds\NBCPaymentGateway\DTOs;

use Lockminds\NBCPaymentGateway\Rules\NBCValidationHelper;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class NBCReceiptDTO extends ValidatedDTO
{
    public string $id;

    public string $customer_msisdn;

    public string $sender_name;

    protected function defaults(): array
    {
        return [];
    }

    /**
     * @return array[]
     */
    protected function rules(): array
    {
        return NBCValidationHelper::accountToWalletRequestValidator();
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
