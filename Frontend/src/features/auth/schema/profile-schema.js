import { z } from "zod";
import { validationMessage } from "@/components/shared/i18n/validationMessage";

export const profileSchema = z.object({
  jobTitle: z.string().min(1, { error: validationMessage("jobTitleRequired") }),
  location: z.string().min(1, { error: validationMessage("locationRequired") }),
  experience: z.string().min(1, { error: validationMessage("experienceRequired") }),
  bio: z.string().optional(),
});
