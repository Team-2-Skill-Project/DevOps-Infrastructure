import { registerRequest } from "../api/auth.api";
import { useApiMutation } from "./useApiMutation";

/**
 * @param {object} params
 * @param {(email: string) => void} params.onVerificationStep
 *   Called after a successful register, so RegisterPage can move its local
 *   wizard state to the OTP step and remember which email to verify.
 */
export const useRegisterMutation = ({ onVerificationStep }) => {
  return useApiMutation({
    mutationFn: registerRequest,
    onSuccess: (data, variables) => {
      onVerificationStep(variables.email);
    },
    // no onError override here — the component reads mutation.error
    // ({ message, fieldErrors }) and maps it onto the form itself
  });
};
