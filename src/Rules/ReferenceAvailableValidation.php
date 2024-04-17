<?php

namespace Lockminds\CrdbPaymentGateway\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

class ReferenceAvailableValidation implements DataAwareRule, ValidationRule
{
    /**
     * The validator instance.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    // ...

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the current validator.
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = DB::table('crdb_requests')->where('reference_id', $value)->first();
        if (empty($data->id)) {
            $fail(__('ReferenceID supplied is not in our record'));
        }
    }
}
