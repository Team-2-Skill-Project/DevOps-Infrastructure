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
import { Loader2 } from "lucide-react";

const RESEND_SECONDS = 30;

function formatTimer(seconds) {
  const m = String(Math.floor(seconds / 60)).padStart(2, "0");
  const s = String(seconds % 60).padStart(2, "0");
  return `${m}:${s}`;
}

/**
 * OtpView — the shared "Verify your email" 6-digit code step.
 * email            — shown in the "we sent a code to ___" line
 * onVerified(code) — called once the 6-digit code passes validation
 * onBack()         — leave the OTP step
 * onResend()       — called when the resend link is used, after the timer runs out
 *
 * Optional overrides — defaults keep the register wizard behaviour:
 * stepLabel        — eyebrow text; pass null to hide it (the reset flow is not step 2 of 4)
 * title            — heading text
 * description      — sub-heading text (pass your own, already-interpolated string)
 * isPending        — true while the verify request is in flight: disables the submit button
 * error            — message to render under the OTP field (server or flow error)
 */
export function OtpView({
  email,
  onVerified,
  onBack,
  onResend,
  stepLabel,
  title,
  description,
  isPending = false,
  error = null,
}) {
  const { t } = useTranslation("common");
  const [secondsLeft, setSecondsLeft] = React.useState(RESEND_SECONDS);

  const eyebrow =
    stepLabel === undefined ? t("auth.register.step", { current: 2 }) : stepLabel;
  const heading = title ?? t("auth.register.verifyTitle");
  const subHeading =
    description ??
    t("auth.register.verifyDescription", {
      email: email || t("auth.register.yourEmail"),
    });

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
      {eyebrow ? (
        <div className="mb-1 text-[11px] font-semibold text-secondary">
          {eyebrow}
        </div>
      ) : null}
      <h1 className="mb-1 font-[DM_Sans] text-[23px] font-bold leading-7 tracking-tight text-ink">
        {heading}
      </h1>
      <p className="mb-5 text-[13.5px] text-muted">{subHeading}</p>

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

          {error ? (
            <p role="alert" className="mt-2 text-[13px] font-medium text-red-500">
              {error}
            </p>
          ) : null}

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
                disabled={isPending}
                className="text-[13px] font-semibold text-primary hover:underline disabled:text-muted"
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
              disabled={!isComplete || isPending}
              className="h-[46px] flex-1 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 disabled:bg-muted/40"
            >
              {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              {t("auth.register.verify")}
            </Button>
          </div>
        </form>
      </Form>
    </div>
  );
}
