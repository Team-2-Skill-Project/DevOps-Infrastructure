import { z } from "zod";

const ACCEPTED_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
];
const MAX_SIZE_MB = 10;

export const cvFileSchema = z
  .instanceof(File, { message: "Please choose a file" })
  .refine((file) => ACCEPTED_TYPES.includes(file.type), {
    message: "File must be a PDF or Word document (.doc/.docx)",
  })
  .refine((file) => file.size <= MAX_SIZE_MB * 1024 * 1024, {
    message: `File must be under ${MAX_SIZE_MB}MB`,
  });
