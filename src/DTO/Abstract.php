<?php

namespace JordanPartridge\GithubClient\DTO;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation;

abstract class Abstract extends Data
{
    /**
     * Convert the DTO to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return parent::toArray();
    }

    /**
     * Get the validation rules that apply to the DTO.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }
}