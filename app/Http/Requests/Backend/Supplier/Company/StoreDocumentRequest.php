<?php

namespace App\Http\Requests\Backend\Supplier\Company;

class StoreDocumentRequest extends ProfileSectionFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function section(): string
    {
        return 'documents';
    }

    public function rules(): array
    {
        $isOther = blank($this->input('document_type_id'));

        return [
            'document_type_id' => 'nullable|integer|exists:document_types,id',
            'custom_name' => $isOther ? 'required|string|max:255' : 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'file' => 'required|file|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'custom_name.required' => 'Please enter a document title.',
            'file.required' => 'Please choose a file to upload.',
        ];
    }
}
