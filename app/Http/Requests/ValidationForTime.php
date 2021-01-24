<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidationForTime extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $message = $validator->errors()->getMessages();
        throw new HttpResponseException(response()->json(['status' => 'error', 'message' => array_values($message)[0][0]], 400));
    }
}
