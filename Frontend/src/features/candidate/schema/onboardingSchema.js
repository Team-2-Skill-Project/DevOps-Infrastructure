import { z } from "zod";

export const requiredFieldSchema = z
  .string()
  .trim()
  .min(1, "Validation Error: This field is required!");
