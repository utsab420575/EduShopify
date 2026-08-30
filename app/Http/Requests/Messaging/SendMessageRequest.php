<?php

namespace App\Http\Requests\Messaging;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'                => ['nullable', 'string', 'max:10000'],
            'reply_to_message_id' => ['nullable', 'integer', 'exists:messages,id'],
            'attachments'         => ['nullable', 'array', 'max:10'],
            'attachments.*'       => [
                'file',
                'max:20480', // 20MB
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,csv,zip,txt',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.max'   => 'Attachment file size may not exceed 20MB.',
            'attachments.*.mimes' => 'Attachment must be a valid document or image (JPG, PNG, WEBP, PDF, DOCX, XLSX, ZIP, TXT).',
        ];
    }
}
