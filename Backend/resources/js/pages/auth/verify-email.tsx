import { router } from '@inertiajs/react';
import VerifyEmailCode from '@/components/verify-email-code';
import TextLink from '@/components/text-link';
import { dashboard, logout } from '@/routes';

export default function VerifyEmail({ email }: { email: string }) {
    return (
        <>
            <VerifyEmailCode
                email={email}
                onVerified={() => router.visit(dashboard())}
            />
            <TextLink href={logout()}>Log out</TextLink>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Email verification',
    description: 'Enter the 6-digit code sent to your email.',
};
