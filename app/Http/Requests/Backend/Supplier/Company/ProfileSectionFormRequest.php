<?php

namespace App\Http\Requests\Backend\Supplier\Company;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * The consolidated Business Profile page (docs/AI/design.md §43) has many
 * independent accordion sections on one URL. Laravel's default failed-
 * validation redirect goes to the referring page, which carries no signal
 * about which section to keep open — and several sections share field
 * names (e.g. "title" on both the Video and Service forms), so guessing
 * the right section from error keys alone would be ambiguous. Redirecting
 * explicitly to the profile route with this section's own `?section=`
 * query hint keeps that unambiguous regardless of field-name overlap.
 */
abstract class ProfileSectionFormRequest extends FormRequest
{
    abstract protected function section(): string;

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            redirect()->route('supplier.company.profile', ['section' => $this->section()])
                ->withErrors($validator)
                ->withInput()
        );
    }
}
