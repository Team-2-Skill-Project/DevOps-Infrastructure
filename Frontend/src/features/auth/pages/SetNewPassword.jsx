import { useState } from "react";
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

export default function SetNewPassword() {
  const { t } = useTranslation("common");
  const localizedPath = useLocalizedPath();
  const [state, setState] = useState("default");

  const form = useForm({
    resolver: zodResolver(newPasswordSchema),
    defaultValues: {
      password: "",
      confirmPassword: "",
    },
  });

  function handleSubmit(data) {
    console.log("New password submitted", data);
    setState("saving");
    setTimeout(() => setState("completed"), 1500);
  }

  return (
    <div className="flex min-h-screen flex-col bg-stone-50 text-slate-800">
      <AuthHeader />

      <main className="flex flex-1 items-center justify-center px-4 py-8">
        <AuthCard className="w-full max-w-120 px-6 py-6 shadow-sm sm:px-8 sm:py-8">
          <AnimatePresence mode="wait">
            {state === "default" && (
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
              </div>
            )}

            {state === "verifying" && (
              <ResetStateView
                tone="blue"
                icon={<RotateCw className="h-7 w-7 animate-spin" />}
                title={t("auth.reset.verifying")}
                desc={t("auth.reset.verifyingDescription")}
              >
                <Button
                  variant="secondary"
                  onClick={() => setState("default")}
                  className="h-auto w-full gap-2 rounded-lg bg-slate-900 py-3 text-sm font-bold text-white hover:bg-slate-800"
                >
                  {t("auth.reset.proceed")}
                  <ArrowRight className="h-3.5 w-3.5" />
                </Button>
              </ResetStateView>
            )}

            {state === "expired" && (
              <ResetStateView
                tone="red"
                icon={<Clock className="h-7 w-7" />}
                title={t("auth.reset.expired")}
                desc={t("auth.reset.expiredDescription")}
              >
                <Button
                  onClick={() => setState("default")}
                  className="h-auto w-full gap-2 rounded-lg bg-orange-600 py-3 text-sm font-bold text-white hover:bg-orange-700"
                >
                  {t("auth.reset.request")}
                  <RotateCw className="h-3.5 w-3.5" />
                </Button>
              </ResetStateView>
            )}

            {state === "saving" && <SavingPassword key="saving" />}

            {state === "completed" && (
              <ResetStateView
                tone="green"
                icon={<Check className="h-7 w-7" />}
                title={t("auth.reset.completed")}
                desc={t("auth.reset.completedDescription")}
              >
                <a
                  href={localizedPath("/dashboard")}
                  className="flex w-full items-center justify-center gap-2 rounded-lg bg-orange-600 py-3 text-sm font-bold text-white hover:bg-orange-700"
                >
                  {t("auth.reset.dashboard")}
                  <ArrowRight className="h-3.5 w-3.5" />
                </a>
              </ResetStateView>
            )}

            {state === "error" && (
              <ResetStateView
                tone="red"
                icon={<TriangleAlert className="h-7 w-7" />}
                title={t("auth.reset.error")}
                desc={t("auth.reset.errorDescription")}
              >
                <Button
                  onClick={() => setState("default")}
                  className="h-auto w-full gap-2 rounded-lg bg-slate-900 py-3 text-sm font-bold text-white hover:bg-slate-800"
                >
                  {t("auth.reset.tryAgain")}
                  <RotateCw className="h-3.5 w-3.5" />
                </Button>
              </ResetStateView>
            )}
          </AnimatePresence>

          <SecurityNotice />
        </AuthCard>
      </main>
    </div>
  );
}
