<?php

declare(strict_types=1);

namespace App\Http\Requests\Invitations;

use App\Concerns\PasswordValidationRules;
use App\Models\Invitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreAcceptInvitationRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'lowercase', 'unique:users,email'],
            'password' => $this->passwordRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $invitation = $this->invitation();

            if ($invitation === null || $invitation->isAccepted() || $invitation->isExpired()) {
                $validator->errors()->add('email', __('invitations.validation.invalid'));

                return;
            }

            if ($invitation->email !== mb_strtolower(mb_trim((string) $this->input('email')))) {
                $validator->errors()->add('email', __('invitations.validation.email_mismatch'));
            }
        });
    }

    public function invitation(): ?Invitation
    {
        $token = (string) $this->route('token');

        return Invitation::query()->where('token', $token)->first();
    }
}
