import { z } from "zod";
import { validationMessage } from "@/components/shared/i18n/validationMessage";

export const newPasswordSchema = z.object({
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
    })
    ,
  confirmPassword: z
    .string()
    .min(1, { error: validationMessage("confirmPasswordRequired") }),
}).refine((data) => data.password === data.confirmPassword, {
  error: validationMessage("passwordMismatch"),
  path: ["confirmPassword"],
});
