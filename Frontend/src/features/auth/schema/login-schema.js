import { z } from "zod"
import { validationMessage } from "@/components/shared/i18n/validationMessage";

export const loginSchema = z.object({
    email: z.string()
      .min(1, { error: validationMessage("required") })
      .email({ error: validationMessage("emailInvalid") }),
    password: z.string().min(8, { error: validationMessage("passwordMin", { count: 8 }) }),
})