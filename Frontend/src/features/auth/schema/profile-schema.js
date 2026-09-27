import { z } from "zod";

export const profileSchema = z.object({
  jobTitle: z.string().min(1, "Job title is required"),
  location: z.string().min(1, "Location is required"),
  experience: z.string().min(1, "Please select your experience"),
  bio: z.string().optional(),
});
