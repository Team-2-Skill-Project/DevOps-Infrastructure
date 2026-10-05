import { useTranslation } from "react-i18next";
import {
  useApiMutation,
  normalizeApiError,
} from "@/features/auth/hooks/useApiMutation";
import { applyAuthStatusMessage } from "@/features/auth/api/auth-error-messages";

/**
 * Auth-domain wrapper over useApiMutation.
 *
 * It normalizes + enriches the failure *inside mutationFn and rethrows it*, so the
 * mutation's own `error` — what pages actually read (error.message, error.status,
 * error.fieldErrors) — is already { status, message, fieldErrors } with `message`
 * swapped for the custom status copy (auth.errors.*) when one matches. Doing it in
 * the onError callback alone would leave pages looking at the raw AxiosError
 * ("Request failed with status code 429").
 *
 * flow   — "forgot" | "otp" | "reset": disambiguates statuses that mean different
 *          things per screen (404 = "no account" vs "invalid/expired code").
 * onError — still receives the enriched, already-normalized error.
 */
export const useAuthApiMutation = ({
  mutationFn,
  flow,
  onSuccess,
  onError,
} = {}) => {
  const { t } = useTranslation("common");

  return useApiMutation({
    mutationFn: async (variables) => {
      try {
        return await mutationFn(variables);
      } catch (error) {
        throw applyAuthStatusMessage(normalizeApiError(error), flow, t);
      }
    },
    onSuccess,
    onError,
  });
};
