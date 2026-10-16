<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $targetMentor = $this->route('mentor') ?? $user?->mentor;

        return $user?->isAdmin()
            || ($user?->isMentor() && $user->mentor && $user->mentor->is($targetMentor));
    }

    public function rules(): array
    {
        return [
            'certificate_signature_file' => [
                'nullable',
                'file',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'extensions:png,jpg,jpeg,webp',
                'max:1024',
            ],
            'certificate_signature_data' => [
                'nullable',
                'string',
                'max:1400000',
            ],
            'certificate_signature' => ['nullable', 'string', 'max:1400000'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (
                ! $this->hasFile('certificate_signature_file')
                && ! filled($this->input('certificate_signature_data'))
                && ! filled($this->input('certificate_signature'))
            ) {
                $validator->errors()->add('certificate_signature_data', 'Gambar tanda tangan wajib diisi.');
            }
        }];
    }
}
