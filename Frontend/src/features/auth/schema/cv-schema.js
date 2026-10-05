import { z } from "zod";
import { validationMessage } from "@/components/shared/i18n/validationMessage";

const ACCEPTED_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
];
const MAX_SIZE_MB = 10;

export const cvFileSchema = z
  .instanceof(File, { error: validationMessage("fileRequired") })
  .refine((file) => ACCEPTED_TYPES.includes(file.type), {
    error: validationMessage("fileType"),
  })
  .refine((file) => file.size <= MAX_SIZE_MB * 1024 * 1024, {
    error: validationMessage("fileMaxSize", { size: MAX_SIZE_MB }),
  });
