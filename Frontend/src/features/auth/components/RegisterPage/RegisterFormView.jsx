import * as React from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { User, Mail } from "lucide-react";

import { registerSchema } from "@/features/auth/schema/auth-schema";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import PasswordInput from "@/features/auth/shared/PasswordInput";
import SequentialFormMessage from "@/features/auth/shared/SequentialFormMessage";
import { useTranslation } from "react-i18next";
import { translateValidationMessage } from "@/components/shared/i18n/validationMessage";


const FIELD_ORDER = [
  "name",
  "email",
  "password",
  "password_confirmation",
  "terms",
];

/**
 * RegisterFormView — step 1 of 4: "Create your account"
 * Pure view: it owns no mutation/network logic. The parent (RegisterPage)
 * wires this to useRegisterMutation and passes down submission state.
 *
 * @param {(data: { name: string, email: string, password: string, password_confirmation: string, terms: boolean }) => void} onSubmit
 * @param {boolean} [isPending] - true while the register request is in flight
 * @param {string|null} [serverError] - non-field-level backend error (e.g. network/500), shown as a banner
 * @param {Record<string, string[]>|null} [fieldErrors] - Laravel 422 shape, e.g. { email: ["already taken"] }.
 *   This component owns the RHF instance, so it's the one place that can call setError with these.
 */
export function RegisterFormView({
  onSubmit,
  isPending = false,
  serverError = null,
  fieldErrors = null,
}) {
  const { t } = useTranslation("common");
  const form = useForm({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      terms: false,
    },
  });

  const { errors } = form.formState;
  const firstErrorField = FIELD_ORDER.find((name) => errors[name]);

  React.useEffect(() => {
    if (!fieldErrors) return;
    Object.entries(fieldErrors).forEach(([field, messages]) => {
      if (FIELD_ORDER.includes(field)) {
        form.setError(field, {
          type: "server",
          message: translateValidationMessage(messages[0]),
        });
      }
    });
    // fieldErrors is a new object reference on every failed mutation, so this
    // re-runs exactly once per server response — not on every render.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fieldErrors]);
  function FormError({ name }) {
    return (
      <SequentialFormMessage
        name={name}
        errors={errors}
        firstErrorField={firstErrorField}
      />
    );
  }

  return (
    <div>
      <div className="mb-1 text-[11px] font-semibold text-secondary">
        {t("auth.register.step", { current: 1 })}
      </div>
      <h1 className="mb-1 font-[DM_Sans] text-[23px] font-bold leading-7 tracking-tight text-ink">
        {t("auth.register.createTitle")}
      </h1>
      <p className="mb-5 text-[13.5px] text-muted">
        {t("auth.register.createDescription")}
      </p>

      {serverError && (
        <div className="mb-3 rounded-lg border border-error/30 bg-error/10 px-3 py-2 text-[12.5px] text-error">
          {serverError.message}
        </div>
      )}

      <Form {...form}>
        <form
          onSubmit={form.handleSubmit(onSubmit)}
          className="flex flex-col gap-3"
        >
          <FormField
            control={form.control}
            name="name"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                  {t("auth.register.fullName")}
                </FormLabel>
                <FormControl>
                  <div className="relative">
                    <User className="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                    <Input
                      placeholder={t("auth.register.fullNamePlaceholder")}
                      className="h-11 rounded-xl border-border ps-9"
                      disabled={isPending}
                      {...field}
                    />
                  </div>
                </FormControl>
                <FormError name="name" />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                  {t("auth.register.email")}
                </FormLabel>
                <FormControl>
                  <div className="relative">
                    <Mail className="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                    <Input
                      type="email"
                      placeholder={t("auth.register.emailPlaceholder")}
                      className="h-11 rounded-xl border-border ps-9"
                      disabled={isPending}
                      {...field}
                    />
                  </div>
                </FormControl>
                <FormError name="email" />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="password"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                  {t("auth.register.password")}
                </FormLabel>
                <FormControl>
                  <PasswordInput
                    showLock
                    placeholder={t("auth.register.passwordPlaceholder")}
                    disabled={isPending}
                    {...field}
                  />
                </FormControl>
                <FormError name="password" />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="password_confirmation"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                  {t("auth.register.confirmPassword")}
                </FormLabel>
                <FormControl>
                  <PasswordInput
                    showLock
                    placeholder={t("auth.register.confirmPasswordPlaceholder")}
                    disabled={isPending}
                    {...field}
                  />
                </FormControl>
                <FormError name="password_confirmation" />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="terms"
            render={({ field }) => (
              <FormItem>
                <div className="mt-1 flex items-start gap-2.5">
                  <FormControl>
                    <Checkbox
                      checked={field.value}
                      onCheckedChange={field.onChange}
                      disabled={isPending}
                      className="mt-0.5 h-5 w-5 rounded-[6px] border-border data-[state=checked]:border-primary data-[state=checked]:bg-primary"
                    />
                  </FormControl>
                  <label className="text-[12.5px] text-ink/80">
                    {t("auth.register.agree")}{" "}
                    <a
                      href="#"
                      className="font-semibold text-primary underline-offset-2 hover:underline"
                    >
                      {t("auth.register.terms")}
                    </a>{" "}
                    and{" "}
                    <a
                      href="#"
                      className="font-semibold text-primary underline-offset-2 hover:underline"
                    >
                      {t("auth.register.privacy")}
                    </a>
                    .
                  </label>
                </div>
                <FormError name="terms" />
              </FormItem>
            )}
          />

          <Button
            type="submit"
            disabled={isPending}
            className="mt-1 h-11.5 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-60"
          >
            {isPending
              ? t("auth.register.submitting")
              : t("auth.register.submit")}
          </Button>
        </form>
      </Form>
    </div>
  );
}
