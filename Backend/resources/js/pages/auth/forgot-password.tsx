import { HttpResponseError } from '@inertiajs/core';
import { Head, router, setLayoutProps, useHttp } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
import {
    resetPassword,
    store,
    verifyOtp,
} from '@/actions/App/Http/Controllers/Auth/EmailPasswordResetController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';

type Step =
    | { name: 'email' }
    | { name: 'otp'; email: string }
    | { name: 'password'; email: string; token: string }
    | { name: 'success' };

function errorMessage(error: unknown): string {
    if (error instanceof HttpResponseError) {
        const body: unknown = error.response.data;
        if (
            error.response.status === 422 &&
            body &&
            typeof body === 'object' &&
            'errors' in body
        ) {
            return '';
        }
        if (
            body &&
            typeof body === 'object' &&
            'message' in body &&
            typeof body.message === 'string'
        ) {
            return body.message;
        }
    }
    return 'Unable to complete the request. Please try again.';
}

function RequestCode({ onSent }: { onSent: (email: string) => void }) {
    const form = useHttp({ email: '' });
    const [message, setMessage] = useState('');

    async function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (form.processing) return;
        setMessage('');
        try {
            await form.post(store.url());
            onSent(form.data.email);
        } catch (error) {
            setMessage(errorMessage(error));
        }
    }

    return (
        <form onSubmit={submit} className="flex flex-col gap-6">
            <fieldset disabled={form.processing} className="grid gap-6">
                <div className="grid gap-2">
                    <Label htmlFor="email">Email address</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        autoComplete="email"
                        autoFocus
                        required
                        placeholder="email@example.com"
                        value={form.data.email}
                        onChange={(event) =>
                            form.setData('email', event.target.value)
                        }
                    />
                    <InputError message={form.errors.email} />
                </div>
                {message && <InputError message={message} role="alert" />}
                <Button
                    type="submit"
                    className="w-full"
                    disabled={form.processing}
                >
                    {form.processing && <Spinner />}
                    Send verification code
                </Button>
            </fieldset>
        </form>
    );
}

function VerifyCode({
    email,
    onVerified,
    onRestart,
}: {
    email: string;
    onVerified: (token: string) => void;
    onRestart: () => void;
}) {
    const form = useHttp<
        { email: string; otp: string },
        { reset_token: string }
    >({ email, otp: '' });
    const resend = useHttp({ email });
    const [message, setMessage] = useState('');
    const [notice, setNotice] = useState('');
    const processing = form.processing || resend.processing;

    async function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (processing) return;
        setMessage('');
        setNotice('');
        if (!/^[0-9]{6}$/.test(form.data.otp)) {
            form.setError('otp', 'Enter the six-digit verification code.');
            return;
        }
        try {
            const response = await form.post(verifyOtp.url());
            if (
                typeof response?.reset_token !== 'string' ||
                !response.reset_token.trim()
            ) {
                setMessage(
                    'Unable to start your password reset session. Please request a new code.',
                );
                return;
            }
            onVerified(response.reset_token);
        } catch (error) {
            setMessage(errorMessage(error));
        }
    }

    async function resendCode() {
        if (processing) return;
        setMessage('');
        setNotice('');
        try {
            await resend.post(store.url());
            form.resetAndClearErrors('otp');
            setNotice('A new code has been sent. Use it within ten minutes.');
        } catch (error) {
            setMessage(errorMessage(error));
        }
    }

    return (
        <form onSubmit={submit} className="flex flex-col gap-6">
            <fieldset disabled={processing} className="grid gap-6">
                <div className="grid gap-2">
                    <Label htmlFor="otp">Verification code</Label>
                    <Input
                        id="otp"
                        name="otp"
                        type="text"
                        inputMode="numeric"
                        autoComplete="one-time-code"
                        pattern="[0-9]{6}"
                        minLength={6}
                        maxLength={6}
                        required
                        autoFocus
                        placeholder="123456"
                        value={form.data.otp}
                        onChange={(event) => {
                            form.setData(
                                'otp',
                                event.target.value
                                    .replace(/\D/g, '')
                                    .slice(0, 6),
                            );
                            form.clearErrors('otp');
                        }}
                    />
                    <InputError message={form.errors.otp} />
                    <InputError message={form.errors.email} />
                    <InputError message={resend.errors.email} />
                </div>
                {message && <InputError message={message} role="alert" />}
                {notice && (
                    <p className="text-muted-foreground text-sm" role="status">
                        {notice}
                    </p>
                )}
                <Button type="submit" className="w-full" disabled={processing}>
                    {form.processing && <Spinner />}
                    Verify code
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    disabled={processing}
                    onClick={resendCode}
                >
                    {resend.processing && <Spinner />}
                    Resend code
                </Button>
                <Button type="button" variant="ghost" onClick={onRestart}>
                    Use a different email address
                </Button>
            </fieldset>
        </form>
    );
}

function NewPassword({
    email,
    token,
    passwordRules,
    onRestart,
    onSuccess,
}: {
    email: string;
    token: string;
    passwordRules: string;
    onRestart: () => void;
    onSuccess: () => void;
}) {
    const form = useHttp({
        email,
        reset_token: token,
        password: '',
        password_confirmation: '',
    });
    const [message, setMessage] = useState('');

    async function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (form.processing) return;
        setMessage('');
        form.clearErrors('password_confirmation');
        if (form.data.password !== form.data.password_confirmation) {
            form.setError(
                'password_confirmation',
                'The passwords do not match.',
            );
            return;
        }
        try {
            await form.post(resetPassword.url());
            form.setData({
                email: '',
                reset_token: '',
                password: '',
                password_confirmation: '',
            });
            onSuccess();
        } catch (error) {
            form.reset('password', 'password_confirmation');
            setMessage(errorMessage(error));
        }
    }

    return (
        <form onSubmit={submit} className="flex flex-col gap-6">
            <fieldset disabled={form.processing} className="grid gap-6">
                <div className="grid gap-2">
                    <Label htmlFor="password">New password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        autoComplete="new-password"
                        required
                        autoFocus
                        placeholder="New password"
                        passwordrules={passwordRules}
                        value={form.data.password}
                        onChange={(event) =>
                            form.setData('password', event.target.value)
                        }
                    />
                    <InputError message={form.errors.password} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="password_confirmation">
                        Confirm password
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autoComplete="new-password"
                        required
                        placeholder="Confirm password"
                        value={form.data.password_confirmation}
                        onChange={(event) =>
                            form.setData(
                                'password_confirmation',
                                event.target.value,
                            )
                        }
                    />
                    <InputError message={form.errors.password_confirmation} />
                </div>
                <InputError message={form.errors.reset_token} />
                <InputError message={form.errors.email} />
                {message && <InputError message={message} role="alert" />}
                <Button
                    type="submit"
                    className="w-full"
                    disabled={form.processing}
                >
                    {form.processing && <Spinner />}
                    Reset password
                </Button>
                <Button type="button" variant="ghost" onClick={onRestart}>
                    Request a new code
                </Button>
            </fieldset>
        </form>
    );
}

function ResetSuccess() {
    useEffect(() => {
        const timeout = window.setTimeout(() => {
            router.visit(login(), { replace: true });
        }, 1500);

        return () => window.clearTimeout(timeout);
    }, []);

    return (
        <p
            className="text-center text-sm font-medium text-green-600"
            role="status"
        >
            Your password has been reset successfully. Redirecting to login…
        </p>
    );
}

export default function ForgotPassword({
    passwordRules,
}: {
    passwordRules: string;
}) {
    const [step, setStep] = useState<Step>({ name: 'email' });
    const restart = () => setStep({ name: 'email' });

    setLayoutProps({
        title:
            step.name === 'email'
                ? 'Forgot password'
                : step.name === 'otp'
                  ? 'Verify code'
                  : step.name === 'password'
                    ? 'Create new password'
                    : 'Password reset',
        description:
            step.name === 'email'
                ? 'Enter your email address and we will send a verification code to your email.'
                : step.name === 'otp'
                  ? `Enter the six-digit code sent to ${step.email}. It expires in ten minutes.`
                  : step.name === 'password'
                    ? 'Choose a new password. Your reset session expires in ten minutes.'
                    : 'You can now log in with your new password.',
    });

    return (
        <>
            <Head title="Forgot password" />
            <div className="space-y-6">
                {step.name === 'email' && (
                    <RequestCode
                        onSent={(email) => setStep({ name: 'otp', email })}
                    />
                )}
                {step.name === 'otp' && (
                    <VerifyCode
                        email={step.email}
                        onRestart={restart}
                        onVerified={(token) =>
                            setStep({
                                name: 'password',
                                email: step.email,
                                token,
                            })
                        }
                    />
                )}
                {step.name === 'password' && (
                    <NewPassword
                        email={step.email}
                        token={step.token}
                        passwordRules={passwordRules}
                        onRestart={restart}
                        onSuccess={() => setStep({ name: 'success' })}
                    />
                )}
                {step.name === 'success' && <ResetSuccess />}
                <div className="text-muted-foreground space-x-1 text-center text-sm">
                    <TextLink href={login()}>Back to login</TextLink>
                </div>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Forgot password',
    description:
        'Enter your email address and we will send a verification code to your email.',
};
