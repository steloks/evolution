<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RankingRequest extends FormRequest
{
    public const string FORMAT = 'Y-m-d\TH:\0\0';

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:'.self::FORMAT],
            'to' => ['required', 'date_format:'.self::FORMAT, 'after:from'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'date_format' => 'The :attribute must be a whole hour like 2026-07-01T15:00.',
        ];
    }
}
