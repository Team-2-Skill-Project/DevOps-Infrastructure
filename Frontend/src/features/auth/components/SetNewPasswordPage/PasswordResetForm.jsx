import React from "react";
import { ArrowRight } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
} from "@/components/ui/form";
import PasswordInput from "@/features/auth/shared/PasswordInput";
import SequentialFormMessage from "@/features/auth/shared/SequentialFormMessage";

const FIELD_ORDER = ["password", "confirmPassword"];

export default function PasswordResetForm({ form, onSubmit }) {
  const { t } = useTranslation("common");
  const { errors } = form.formState;
  const firstErrorField = FIELD_ORDER.find((fieldName) => errors[fieldName]);

  return (
    <Form {...form}>
      <form
        noValidate
        onSubmit={form.handleSubmit(onSubmit)}
        className="w-full"
      >
        <FormField
          control={form.control}
          name="password"
          render={({ field }) => (
            <FormItem className="mb-4">
              <FormLabel className="font-bold text-slate-900">
                {t("auth.reset.newPassword")}
              </FormLabel>
              <FormControl>
                <PasswordInput
                  showLock
                  placeholder={t("auth.reset.newPasswordPlaceholder")}
                  {...field}
                />
              </FormControl>
              <SequentialFormMessage
                name="password"
                errors={errors}
                firstErrorField={firstErrorField}
              />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="confirmPassword"
          render={({ field }) => (
            <FormItem className="mb-4">
              <FormLabel className="font-bold text-slate-900">
                {t("auth.reset.confirmPassword")}
              </FormLabel>
              <FormControl>
                <PasswordInput
                  showLock
                  placeholder={t("auth.reset.confirmPasswordPlaceholder")}
                  {...field}
                />
              </FormControl>
              <SequentialFormMessage
                name="confirmPassword"
                errors={errors}
                firstErrorField={firstErrorField}
              />
            </FormItem>
          )}
        />

        <Button
          type="submit"
          className="h-auto w-full cursor-pointer gap-2 rounded-lg bg-primary py-3 text-sm font-bold text-white hover:bg-primary/90"
        >
          {t("auth.reset.save")}
          <ArrowRight className="h-3.5 w-3.5" />
        </Button>
      </form>
    </Form>
  );
}
