import { useMutation } from "@tanstack/react-query";

/**
 * Thin wrapper around useMutation. Adds nothing except one shared job:
 * normalize every backend error response into { status, message, fieldErrors }
 * so every feature hook (auth, candidate, admin...) gets the same error shape
 * for free instead of parsing error.response.data.errors itself.
 *
 * No redirect / toast logic here on purpose — each feature hook decides what
 * "success" or "failure" means for its own flow.
 */
export const useApiMutation = ({ mutationFn, ...options }) => {
  return useMutation({
    // Normalizing inside mutationFn (not inside onError) means the thrown value
    // becomes mutation.error itself, so components and onError callbacks both
    // see { status, message, fieldErrors } instead of the raw AxiosError.
    mutationFn: async (variables) => {
      try {
        return await mutationFn(variables);
      } catch (error) {
        throw normalizeApiError(error);
      }
    },
    ...options,
  });
};



/**
 * Axios failure → { status, message, fieldErrors }.
 * Idempotent: an error already in that shape (thrown enriched by useAuthApiMutation)
 * passes through untouched, so a second pass cannot wipe message/fieldErrors.
 */
export function normalizeApiError(error) {
  if (
    error &&
    typeof error === "object" &&
    !error.response &&
    "fieldErrors" in error &&
    "status" in error
  ) {
    return error;
  }

  const status = error?.response?.status;
  const data = error?.response?.data;

  return {
    status,
    message: data?.message ?? error.message ?? "Something went wrong",
    // Laravel 422 shape: { errors: { email: ["..."], password: ["..."] } }
    fieldErrors: data?.errors ?? null,
  };
}
