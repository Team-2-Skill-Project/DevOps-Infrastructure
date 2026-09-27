import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { z } from "zod";
import { Mail, ArrowRight, Loader2, RotateCcw } from "lucide-react";
import { useTranslation } from "react-i18next";

import logoIcon from "@/assets/logo/MatchIn_logo.svg";
import AuthHeader from "@/features/auth/shared/AuthHeader";
import SecurityNotice from "@/features/auth/shared/SecurityNotice";

// 1. Zod Schema
const emailSchema = z
  .string()
  .trim()
  .min(1, { message: "Please enter a valid email address." })
  .email({ message: "Please enter a valid email address." });

export default function ForgetPasswordPage() {
  const { t } = useTranslation("common");

  const [email, setEmail] = useState("");
  const [emailError, setEmailError] = useState("");
  const [status, setStatus] = useState(false);
  const [message, setMessage] = useState("");
  const [isSuccess, setIsSuccess] = useState(false);
  const [isTimeout, setIsTimeout] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsTimeout(false);
    setMessage("");
    setIsSuccess(false);

    const result = emailSchema.safeParse(email);

    if (!result.success) {
      setEmailError(t("auth.forgot.invalidEmail"));
      return;
    }

    setEmailError("");
    setStatus(true);

    try {
      await new Promise((resolve) => setTimeout(resolve, 2000));
      setIsSuccess(true);
      setMessage(t("auth.forgot.success"));
    } catch (error) {
      setIsTimeout(true);
      setMessage(t("auth.forgot.failure"));
    } finally {
      setStatus(false);
    }
  };

  return (
    <div className="min-h-screen overflow-hidden bg-[#FDFBF9] text-[#1F365C] flex flex-col justify-between p-4 md:p-6 font-sans">
      <AuthHeader bordered={false} backLabel={t("auth.forgot.back")} />

      <main className="max-w-md w-full mx-auto my-auto py-2">
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 flex flex-col items-center text-center">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100/80 text-gray-600 text-xs font-medium mb-6">
            <span className="w-2 h-2 rounded-full bg-[#D06B4F]"></span>
            {t("auth.forgot.badge")}
          </div>

          <img
            src={logoIcon}
            alt="MatchIn Logo"
            className="h-14 w-auto mb-4 object-contain"
          />

          <h1 className="text-2xl md:text-3xl font-extrabold text-[#1F365C] mb-2 tracking-tight">
            {t("auth.forgot.title")}
          </h1>

          <p className="text-sm text-gray-500 max-w-sm mb-6 leading-relaxed">
            {t("auth.forgot.description")}
          </p>

          <form
            onSubmit={handleSubmit}
            noValidate
            className="w-full text-left space-y-4"
          >
            <div className="space-y-1.5">
              <Label
                htmlFor="email"
                className="text-xs font-bold text-[#1F365C]"
              >
                {t("auth.forgot.email")}
              </Label>

              <div className="relative">
                <Mail className="w-4 h-4 absolute inset-s-3 top-1/2 -translate-y-1/2 text-gray-400" />

                <Input
                  id="email"
                  type="text"
                  placeholder="tariq.mansour@company.com"
                  value={email}
                  onChange={(e) => {
                    setEmail(e.target.value);
                    if (emailError) setEmailError("");
                  }}
                  className={`ps-9 h-11 border-gray-200 focus-visible:ring-[#1F365C] ${
                    emailError
                      ? "border-red-500 focus-visible:ring-red-500"
                      : ""
                  }`}
                />
              </div>

              {emailError ? (
                <p className="text-[11px] text-red-500 mt-1 font-medium">
                  {emailError}
                </p>
              ) : null}
            </div>

            <Button
              type="submit"
              disabled={status}
              className="w-full h-11 bg-[#1F365C] hover:bg-[#162744] text-white font-medium flex items-center justify-center gap-2 rounded-lg cursor-pointer"
            >
              {status ? (
                <>
                  <Loader2 className="w-4 h-4 animate-spin" />
                  {t("auth.forgot.sending")}
                </>
              ) : isTimeout ? (
                <>
                  {t("auth.forgot.retry")} <RotateCcw className="w-4 h-4" />
                </>
              ) : (
                <>
                  {t("auth.forgot.send")} <ArrowRight className="w-4 h-4" />
                </>
              )}
            </Button>
          </form>

          <SecurityNotice text={t("auth.forgot.security")} className="w-full" />

          {message && (
            <div
              className={`w-full mt-4 p-3 rounded-xl text-xs text-center font-medium border ${
                isSuccess
                  ? "bg-emerald-50 border-emerald-200 text-emerald-800"
                  : "bg-[#FDF2F2] border-[#FAD2D2] text-[#E05252]"
              }`}
            >
              {message}
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
