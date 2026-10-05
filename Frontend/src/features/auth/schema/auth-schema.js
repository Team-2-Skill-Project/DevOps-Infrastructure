import { z } from "zod";
import { validationMessage } from "@/components/shared/i18n/validationMessage";

export const registerSchema = z
  .object({
    name: z
      .string()
      .min(1, { error: validationMessage("required") })
      .min(3, { error: validationMessage("fullNameMin", { count: 3 }) }),
    email: z
      .string()
      .min(1, { error: validationMessage("required") })
      .email({ error: validationMessage("emailInvalid") }),
    password: z
      .string()
      .min(8, { error: validationMessage("passwordMin", { count: 8 }) })
      .regex(/[A-Z]/, {
        error: validationMessage("passwordUppercase"),
      })
      .regex(/[a-z]/, {
        error: validationMessage("passwordLowercase"),
      })
      .regex(/[0-9]/, { error: validationMessage("passwordNumber") })
      .regex(/[^A-Za-z0-9]/, {
        error: validationMessage("passwordSpecial"),
      }),
    password_confirmation: z
      .string()
      .min(1, { error: validationMessage("confirmPasswordRequired") }),
    terms: z.literal(true, {
      error: validationMessage("acceptTerms"),
    }),
  })
  .refine((data) => data.password === data.password_confirmation, {
    error: validationMessage("passwordMismatch"),
    path: ["password_confirmation"],
  });

export const otpSchema = z.object({
  code: z
    .string()
    .length(6, { error: validationMessage("otpLength", { count: 6 }) })
    .regex(/^\d{6}$/, { error: validationMessage("otpNumbersOnly") }),
});
