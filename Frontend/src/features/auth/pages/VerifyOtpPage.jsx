import React from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

import logoIcon from "@/assets/logo/MatchIn_logo.svg";
import AuthHeader from "@/features/auth/shared/AuthHeader";
import SecurityNotice from "@/features/auth/shared/SecurityNotice";
import { OtpView } from "@/features/auth/components/RegisterPage/OtpView";
import { useResendOtpMutation } from "@/features/auth/hooks/useResendOtpMutation";
import { useLocalizedPath } from "@/utils/routes";
import { useVerifyForgotOtpMutation } from "../hooks/useVerifyForgotOtpMutation";

// Password-recovery OTP step: verifies the 6-digit code, then continues to /reset-password.
export default function VerifyOtpPage() {
  const { t } = useTranslation("common");
  const localizedPath = useLocalizedPath();
  const navigate = useNavigate();

  // Email + OTP token live in router state only — server flow data, not durable client state, so never Redux.
  const { email = "" } = useLocation().state ?? {};

  const verify = useVerifyForgotOtpMutation({
    onSuccess: ({ token }) =>
      navigate(localizedPath("/auth/reset-password"), {
        state: { email, token },
      }),
  });
  const resend = useResendOtpMutation();

  const error = verify.isError
    ? verify.error?.message || t("auth.forgot.failure")
    : resend.isError
      ? resend.error?.message || t("auth.forgot.failure")
      : null;

  const goBackToRequest = () =>
    navigate(localizedPath("/auth/forgot-password"));

  return (
    <div className="min-h-screen overflow-hidden bg-[#FDFBF9] text-primary flex flex-col justify-between p-4 md:p-6 font-sans">
      <AuthHeader
        bordered={false}
        backTo="/auth/forgot-password"
        backLabel={t("auth.forgot.otpBack")}
      />

      <main className="max-w-md w-full mx-auto my-auto py-2">
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 flex flex-col items-center text-center">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100/80 text-gray-600 text-xs font-medium mb-6">
            <span className="w-2 h-2 rounded-full bg-secondary"></span>
            {t("auth.forgot.badge")}
          </div>

          <img
            src={logoIcon}
            alt="MatchIn Logo"
            className="h-14 w-auto mb-4 object-contain"
          />

          {email ? (
            <div className="w-full">
              <OtpView
                email={email}
                onVerified={(code) => {
                  resend.reset();
                  verify.mutate({ email, code });
                }}
                onBack={goBackToRequest}
                onResend={() => {
                  verify.reset();
                  resend.mutate({ email });
                }}
                stepLabel={null}
                title={t("auth.forgot.otpTitle")}
                description={t("auth.forgot.otpDescription", { email })}
                isPending={verify.isPending}
                error={error}
              />
            </div>
          ) : (
            // Refreshed / directly-opened URL: router state is gone, so there is no code to send to.
            <div className="w-full space-y-3">
              <h1 className="text-2xl md:text-3xl font-extrabold text-primary tracking-tight">
                {t("auth.forgot.otpTitle")}
              </h1>
              <p className="text-sm text-gray-500 leading-relaxed">
                {t("auth.forgot.otpMissingEmail")}
              </p>
              <Button
                onClick={goBackToRequest}
                className="w-full h-11 bg-primary hover:bg-[#162744] text-white font-medium rounded-lg cursor-pointer"
              >
                {t("auth.forgot.otpRequestNew")}
              </Button>
            </div>
          )}

          <SecurityNotice text={t("auth.forgot.security")} className="w-full" />
        </div>
      </main>
    </div>
  );
}
