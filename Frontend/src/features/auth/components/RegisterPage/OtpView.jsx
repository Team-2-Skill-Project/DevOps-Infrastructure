import * as React from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";

import { otpSchema } from "@/features/auth/schema/auth-schema";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormMessage,
} from "@/components/ui/form";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/components/ui/input-otp";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

const RESEND_SECONDS = 30;

function formatTimer(seconds) {
  const m = String(Math.floor(seconds / 60)).padStart(2, "0");
  const s = String(seconds % 60).padStart(2, "0");
  return `${m}:${s}`;
}

/**
 * OtpView — step 2 of 4: "Verify your email"
 * email            — shown in the "we sent a code to ___" line
 * onVerified(code) — called once the 6-digit code passes validation
 * onBack()         — go back to the register form
 * onResend()       — called when the resend link is used, after the timer runs out
 */
export function OtpView({ email, onVerified, onBack, onResend }) {
  const { t } = useTranslation("common");
  const [secondsLeft, setSecondsLeft] = React.useState(RESEND_SECONDS);

  const form = useForm({
    resolver: zodResolver(otpSchema),
    defaultValues: { code: "" },
  });

  React.useEffect(() => {
    if (secondsLeft <= 0) return;
    const id = setInterval(() => setSecondsLeft((s) => s - 1), 1000);
    return () => clearInterval(id);
  }, [secondsLeft]);

  const code = form.watch("code");
  const isComplete = code?.length === 6;

  const handleResend = () => {
    form.reset({ code: "" });
    setSecondsLeft(RESEND_SECONDS);
    onResend?.();
  };

  return (
    <div>
      <div className="mb-1 text-[11px] font-semibold text-secondary">
        {t("auth.register.step", { current: 2 })}
      </div>
      <h1 className="mb-1 font-[DM_Sans] text-[23px] font-bold leading-7 tracking-tight text-ink">
        {t("auth.register.verifyTitle")}
      </h1>
      <p className="mb-5 text-[13.5px] text-muted">
        {t("auth.register.verifyDescription", {
          email: email || t("auth.register.yourEmail"),
        })}
      </p>

      <Form {...form}>
        <form onSubmit={form.handleSubmit((data) => onVerified?.(data.code))}>
          <FormField
            control={form.control}
            name="code"
            render={({ field }) => (
              <FormItem>
                <FormControl>
                  <InputOTP maxLength={6} {...field}>
                    <InputOTPGroup className="w-full justify-between gap-2.5">
                      {Array.from({ length: 6 }).map((_, i) => (
                        <InputOTPSlot
                          key={i}
                          index={i}
                          className="h-[50px] w-full flex-1 rounded-[8px] border-border font-[DM_Sans] text-[19px] text-primary font-semibold text-ink data-[active=true]:border-primary"
                        />
                      ))}
                    </InputOTPGroup>
                  </InputOTP>
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          <div className="mt-2.5 text-[13px] text-muted">
            {secondsLeft > 0 ? (
              <span>
                {t("auth.register.resendIn")}{" "}
                <strong className="text-ink">{formatTimer(secondsLeft)}</strong>
              </span>
            ) : (
              <button
                type="button"
                onClick={handleResend}
                className="text-[13px] font-semibold text-primary hover:underline"
              >
                {t("auth.register.resend")}
              </button>
            )}
          </div>

          <div className="mt-7 flex gap-3">
            <Button
              type="button"
              variant="outline"
              onClick={onBack}
              className="h-[46px] rounded-xl border-border px-5 hover:text-surface"
            >
              {t("auth.register.back")}
            </Button>
            <Button
              type="submit"
              disabled={!isComplete}
              className="h-[46px] flex-1 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 disabled:bg-muted/40"
            >
              {t("auth.register.verify")}
            </Button>
          </div>
        </form>
      </Form>
    </div>
  );
}
