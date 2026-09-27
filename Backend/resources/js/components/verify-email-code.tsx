import { HttpResponseError } from '@inertiajs/core';
import { Head, setLayoutProps, useHttp } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import {
    verifyEmailOtp,
    resendEmailOtp,
} from '@/actions/App/Http/Controllers/Auth/AuthController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

function requestErrorMessage(error: unknown): string {
    if (error instanceof HttpResponseError) {
        const body: unknown = error.response.data;

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

export default function VerifyEmailCode({
    email,
    onVerified,
}: {
    email: string;
    onVerified: () => void;
}) {
    const { data, setData, post, errors, processing, resetAndClearErrors } =
        useHttp({
            email,
            otp: '',
        });
    const [message, setMessage] = useState('');
    const [notice, setNotice] = useState('');
    const resend = useHttp({ email });
    const busy = processing || resend.processing;

    async function resendCode() {
        if (busy) return;
        setMessage('');
        setNotice('');
        try {
            await resend.post(resendEmailOtp.url());
            resetAndClearErrors('otp');
            setNotice('A new code has been sent to your email.');
        } catch (error) {
            setMessage(requestErrorMessage(error));
        }
    }

    setLayoutProps({
        title: 'Verify your email address',
        description: `Enter the 6-digit code sent to your email: ${email}. It expires in ten minutes.`,
    });

    async function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (busy) return;
        setMessage('');
        setNotice('');

        try {
            await post(verifyEmailOtp.url());
            onVerified();
        } catch (error) {
            setMessage(requestErrorMessage(error));
        }
    }

    return (
        <>
            <Head title="Verify email address" />
            <form onSubmit={submit} className="flex flex-col gap-6">
                <fieldset disabled={busy} className="grid gap-6">
                    <input type="hidden" name="email" value={data.email} />
                    <InputError message={errors.email} />
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
                            value={data.otp}
                            onChange={(event) =>
                                setData('otp', event.target.value)
                            }
                        />
                        <InputError message={errors.otp} />
                    </div>
                    {message && <InputError message={message} role="alert" />}
                    {notice && <p role="status">{notice}</p>}
                    <Button
                        type="submit"
                        className="mt-2 w-full"
                        disabled={busy}
                    >
                        {processing && <Spinner />}
                        Verify email address
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={resendCode}
                        disabled={busy}
                    >
                        Resend code
                    </Button>
                </fieldset>
            </form>
        </>
    );
}
