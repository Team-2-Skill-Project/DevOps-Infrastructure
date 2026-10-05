import { z } from "zod";
import { validationMessage } from "@/components/shared/i18n/validationMessage";

export const requiredFieldSchema = z
  .string()
  .trim()
  .min(1, { error: validationMessage("required") });
