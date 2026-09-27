import React from "react";
import { ArrowRight } from "lucide-react";
import { Link } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/utils/routes";

import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
} from "@/components/ui/form";
import {
  Field,
  FieldGroup,
  FieldLabel,
  FieldSeparator,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import google from "@/assets/icons/google-icon.svg";

import LoginHeading from "./LoginHeading";
import PasswordInput from "@/features/auth/shared/PasswordInput";
import SequentialFormMessage from "@/features/auth/shared/SequentialFormMessage";
import { loginSchema } from "../../schema/login-schema";

const FIELD_ORDER = ["email", "password"];

export default function LoginForm({ onSubmit }) {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("common");

  const form = useForm({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: "",
      password: "",
      remember_me: false,
    },
  });

  const { errors } = form.formState;
  const firstErrorField = FIELD_ORDER.find((name) => errors[name]);

  const handleFormSubmit = (data) => {
    if (onSubmit) {
      onSubmit(data);
    }
  };

  return (
    <Form {...form}>
      <form noValidate onSubmit={form.handleSubmit(handleFormSubmit)}>
        <LoginHeading
          title={t("auth.login.welcome")}
          subTitle={t("auth.login.subtitle")}
        />
        <FieldGroup className="gap-5">
          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11px] font-bold uppercase tracking-[0.5px] text-[#64748b]">
                  {t("auth.login.email")}
                </FormLabel>
                <FormControl>
                  <Input
                    type="email"
                    placeholder={t("auth.login.emailPlaceholder")}
                    className="h-11.5 w-full rounded-[8px] border border-[#e2e8f0] bg-white px-4 py-3.5 text-[16px] text-[#0f172a] outline-none transition-all placeholder:text-[#e2e8f0] focus-visible:border-[#1d3557]"
                    {...field}
                  />
                </FormControl>
                <SequentialFormMessage
                  name="email"
                  errors={errors}
                  firstErrorField={firstErrorField}
                />
              </FormItem>
            )}
          />

          <FormField
            control={form.control}
            name="password"
            render={({ field }) => (
              <FormItem>
                <FormLabel className="text-[11px] font-bold uppercase tracking-[0.5px] text-[#64748b]">
                  {t("auth.login.password")}
                </FormLabel>
                <FormControl>
                  <PasswordInput
                    placeholder={t("auth.login.passwordPlaceholder")}
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

          <Field className="flex flex-row items-center justify-between w-full mb-1">
            <FieldLabel className="font-normal text-[#64748b] sm:text-[13px] text-[11px] cursor-pointer">
              <FormField
                control={form.control}
                name="remember_me"
                render={({ field }) => (
                  <FormControl>
                    <Checkbox
                      checked={field.value}
                      onCheckedChange={field.onChange}
                      className="h-4 w-4 rounded-md p-0 outline-0 transition-all"
                    />
                  </FormControl>
                )}
              />
              {t("auth.login.remember")}
            </FieldLabel>
            <Link
              to={localizedPath("/auth/forgot-password")}
              className="flex justify-end sm:text-[13px] text-[#2563eb] hover:underline font-medium text-[11px]"
            >
              {t("auth.login.forgot")}
            </Link>
          </Field>

          <Field>
            <Button
              type="submit"
              className="flex justify-center gap-2 duration-200 h-11.5 p-3.5 bg-primary hover:bg-primary/90 rounded-[8px] text-[15px] font-semibold cursor-pointer"
            >
              {t("auth.login.submit")}{" "}
              <ArrowRight width={20} className="font-bold" />
            </Button>
          </Field>

          <FieldSeparator className="text-[#64748b] text-[12px] px-3.5 my-1">
            {t("auth.login.or")}
          </FieldSeparator>

          <Field>
            <Button
              type="button"
              className="p-3 h-11 bg-white hover:bg-[#f8fafc] border-[#e2e8f0] border rounded-[8px] text-[#0f172a] text-[14px] font-semibold cursor-pointer flex items-center justify-center gap-2.5 duration-200"
            >
              <img src={google} alt="Google" width={18} height={18} />
              {t("auth.login.google")}
            </Button>
          </Field>
        </FieldGroup>
      </form>
    </Form>
  );
}
