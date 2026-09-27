<x-mail::message>
# SkillMatch

{{ $purpose === \App\Models\EmailOtp::PASSWORD_RESET ? __('auth.mail.reset_password') : __('auth.mail.verify_email') }}

{{ __('auth.mail.verification_code') }}

<x-mail::panel>
{{ $code }}
</x-mail::panel>

{{ __('auth.mail.expires', ['time' => $expiresAt->format('H:i T')]) }}

{{ __('auth.mail.security_notice') }}

{{ __('auth.mail.team') }}
</x-mail::message>
