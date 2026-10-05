import { AnimatePresence } from "framer-motion";
import {
  ArrowRight,
  Check,
  Clock,
  RotateCw,
  TriangleAlert,
} from "lucide-react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Link, useLocation, useNavigate } from "react-router-dom";
import AuthCard from "@/features/auth/shared/AuthCard";
import { Button } from "@/components/ui/button";
import PasswordResetForm from "@/features/auth/components/SetNewPasswordPage/PasswordResetForm";
import AuthHeader from "@/features/auth/shared/AuthHeader";
import ResetStateView from "@/features/auth/components/SetNewPasswordPage/ResetStateView";
import SavingPassword from "@/features/auth/components/SetNewPasswordPage/SavingPassword";
import SecurityNotice from "@/features/auth/shared/SecurityNotice";
import logo from "@/assets/logo/MatchIn_logo.svg";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/utils/routes";
import { newPasswordSchema } from "../schema/newPassword-schema";
import { translateValidationMessage } from "@/components/shared/i18n/validationMessage";
import { useResetPasswordMutation } from "@/features/auth/hooks/useResetPasswordMutation";
import {
  isExpiredAuthStatus,
  isServerErrorStatus,
} from "@/features/auth/api/auth-error-messages";

// Laravel 422 field name → RHF field name, so server errors land in the right input.
const SERVER_TO_FORM_FIELD = {
  password: "password",
  password_confirmation: "confirmPassword",
};

export default function SetNewPassword() {
  const { t } = useTranslation("common");
  const localizedPath = useLocalizedPath();
  const navigate = useNavigate();

  // { email, token } arrives in router state from the OTP step — server flow data, not durable client state, so never Redux.
  const { email = "", token = "" } = useLocation().state ?? {};

  const form = useForm({
    resolver: zodResolver(newPasswordSchema),
    defaultValues: {
      password: "",
      confirmPassword: "",
    },
  });

  // No local loading/success booleans: pending, success and error are all read
  // off the mutation (AUTH_LOGIC_CONVENTION §4.3), like ForgetPasswordPage.
  const reset = useResetPasswordMutation({
    onError: (error) => {
      if (!error.fieldErrors) return;
      // Convention §5: normalized fieldErrors go straight into RHF.
      Object.entries(error.fieldErrors).forEach(([field, messages]) => {
        const name = SERVER_TO_FORM_FIELD[field] ?? field;
        form.setError(name, {
          type: "server",
          message: translateValidationMessage(
            Array.isArray(messages) ? messages[0] : messages,
          ),
        });
      });
    },
  });

  const fieldErrors = reset.error?.fieldErrors;
  const hasServerFieldErrors = Boolean(
    fieldErrors && Object.keys(fieldErrors).length
  );

  // Refreshed / directly-opened URL: router state is gone, so there is no token to reset with.
  const missingSession = !email || !token;
  const showExpiredView =
    missingSession || (reset.isError && isExpiredAuthStatus(reset.error?.status));
  const showServerErrorView =
    reset.isError && isServerErrorStatus(reset.error?.status);
  const showBanner =
    reset.isError &&
    !showExpiredView &&
    !showServerErrorView &&
    !hasServerFieldErrors;

  function handleSubmit(data) {
    reset.mutate({
      email,
      token,
      password: data.password,
      password_confirmation: data.confirmPassword,
    });
  }

  return (
    <div className="flex min-h-screen flex-col bg-stone-50 text-slate-800">
      <AuthHeader />

      <main className="flex flex-1 items-center justify-center px-4 py-8">
        <AuthCard className="w-full max-w-120 px-6 py-6 shadow-sm sm:px-8 sm:py-8">
          <AnimatePresence mode="wait">
            {reset.isPending && <SavingPassword key="saving" />}

            {!reset.isPending && showExpiredView && (
              <ResetStateView
                key="expired"
                tone="red"
                icon={<Clock className="h-7 w-7" />}
                title={t("auth.reset.expired")}
                desc={
                  reset.error?.message || t("auth.reset.expiredDescription")
                }
              >
                <Button
                  onClick={() => navigate(localizedPath("/auth/forgot-password"))}
                  className="h-auto w-full gap-2 rounded-lg bg-red-600 py-3 text-sm font-bold text-white hover:bg-red-700"
                >
                  {t("auth.reset.request")}
                  <RotateCw className="h-3.5 w-3.5" />
                </Button>
              </ResetStateView>
            )}

            {!reset.isPending && !showExpiredView && showServerErrorView && (
              <ResetStateView
                key="error"
                tone="red"
                icon={<TriangleAlert className="h-7 w-7" />}
                title={t("auth.reset.error")}
                desc={
                  reset.error?.message || t("auth.reset.errorDescription")
                }
              >
                <Button
                  onClick={() => reset.reset()}
                  className="h-auto w-full gap-2 rounded-lg bg-slate-900 py-3 text-sm font-bold text-white hover:bg-slate-800"
                >
                  {t("auth.reset.tryAgain")}
                  <RotateCw className="h-3.5 w-3.5" />
                </Button>
              </ResetStateView>
            )}

            {!reset.isPending && reset.isSuccess && (
              <ResetStateView
                key="completed"
                tone="green"
                icon={<Check className="h-7 w-7" />}
                title={t("auth.reset.completed")}
                desc={t("auth.reset.completedDescription")}
              >
                <Link
                  to={localizedPath("/dashboard")}
                  className="flex w-full items-center justify-center gap-2 rounded-lg bg-orange-600 py-3 text-sm font-bold text-white hover:bg-orange-700"
                >
                  {t("auth.reset.dashboard")}
                  <ArrowRight className="h-3.5 w-3.5" />
                </Link>
              </ResetStateView>
            )}

            {!reset.isPending &&
              !showExpiredView &&
              !showServerErrorView &&
              !reset.isSuccess && (
                <div key="default" className="flex w-full flex-col items-center">
                  <div className="mb-6 flex flex-col items-center text-center">
                    <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl text-white">
                      <img src={logo} alt="MatchIn" className="h-full w-full" />
                    </div>
                    <span className="mb-1 flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-[11px] text-slate-600">
                      <span className="h-1.5 w-1.5 rounded-full bg-red-500" />
                      {t("auth.reset.badge")}
                    </span>
                    <h2 className="mt-3 mb-1.5 text-xl font-bold text-slate-900">
                      {t("auth.reset.title")}
                    </h2>
                    <p className="max-w-[320px] text-xs leading-relaxed text-slate-500">
                      {t("auth.reset.description")}
                    </p>
                  </div>

                  <PasswordResetForm form={form} onSubmit={handleSubmit} />

                  {showBanner && (
                    <div className="mt-4 w-full rounded-xl border border-[#FAD2D2] bg-[#FDF2F2] p-3 text-center text-xs font-medium text-[#E05252]">
                      {reset.error?.message}
                    </div>
                  )}
                </div>
              )}
          </AnimatePresence>

          <SecurityNotice />
        </AuthCard>
      </main>
    </div>
  );
}
