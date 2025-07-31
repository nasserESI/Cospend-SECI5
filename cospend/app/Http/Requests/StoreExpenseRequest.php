<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_id' => 'required|integer|exists:groups,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'from' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|gt:0',
            'desc' => 'nullable|string',
            'to' => 'required|array',
            'to.*' => 'integer|exists:users,id',
        ];
    }

    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();
        $this->redirect = $url->route('groups.show', ['group' => $this->group_id]);
        return $this->redirect;
    }
}