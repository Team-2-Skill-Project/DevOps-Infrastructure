import { useAuthApiMutation } from "@/features/auth/hooks/useAuthApiMutation";
import { forgotPassword } from "@/features/auth/api/auth.api";

// Sends { email } to request a password-reset link; resolves with the server response, rejects with { status, message, fieldErrors } (message = custom status copy when one exists).
export const useForgotPasswordMutation = ({ onSuccess, onError } = {}) => {
  return useAuthApiMutation({
    mutationFn: forgotPassword,
    flow: "forgot",
    onSuccess,
    onError,
  });
};
