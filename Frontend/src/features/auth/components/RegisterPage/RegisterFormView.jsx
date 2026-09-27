import * as React from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { AnimatePresence, motion } from "framer-motion";
import { User, Mail } from "lucide-react";

import { registerSchema } from "@/features/auth/schema/auth-schema";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import PasswordInput from "@/features/auth/shared/PasswordInput";
import SequentialFormMessage from "@/features/auth/shared/SequentialFormMessage";
import { useTranslation } from "react-i18next";

const FIELD_ORDER = [
  "fullName",
  "email",
  "password",
  "confirmPassword",
  "terms",
];

/**
 * RegisterFormView — step 1 of 4: "Create your account"
 * onSubmit(data) — validated { fullName, email, password, confirmPassword, terms }
 */
export function RegisterFormView({ onSubmit }) {
  const { t } = useTranslation("common");
  const form = useForm({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      fullName: "",
      email: "",
      password: "",
      confirmPassword: "",
      terms: false,
    },
  });

  const { errors } = form.formState;
  const firstErrorField = FIELD_ORDER.find((name) => errors[name]);

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

      <Form {...form}>
        <form
          onSubmit={form.handleSubmit(onSubmit)}
          className="flex flex-col gap-3"
        >
          <FormField
            control={form.control}
            name="fullName"
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
                      {...field}
                    />
                  </div>
                </FormControl>
                <FormError name="fullName" />
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
                    {...field}
                  />
                </FormControl>
                <FormError name="password" />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="confirmPassword"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                  {t("auth.register.confirmPassword")}
                </FormLabel>
                <FormControl>
                  <PasswordInput
                    showLock
                    placeholder={t("auth.register.confirmPasswordPlaceholder")}
                    {...field}
                  />
                </FormControl>
                <FormError name="confirmPassword" />
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
            className="mt-1 h-11.5 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
          >
            {t("auth.register.submit")}
          </Button>
        </form>
      </Form>
    </div>
  );
}
