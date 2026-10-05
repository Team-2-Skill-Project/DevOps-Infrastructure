import { useAuthApiMutation } from "@/features/auth/hooks/useAuthApiMutation";
import { resetPassword } from "@/features/auth/api/auth.api";

// Sends { email, token, password, password_confirmation } to set a new password; resolves with the server response, rejects with { status, message, fieldErrors } (message = custom status copy when one exists).
export const useResetPasswordMutation = ({ onSuccess, onError } = {}) => {
  return useAuthApiMutation({
    mutationFn: resetPassword,
    flow: "reset",
    onSuccess,
    onError,
  });
};
