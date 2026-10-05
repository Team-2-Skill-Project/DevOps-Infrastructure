import { useAuthApiMutation } from "@/features/auth/hooks/useAuthApiMutation";
import { resendOtp } from "@/features/auth/api/auth.api";

// Sends { email } to request a fresh 6-digit code; resolves with the server response, rejects with { status, message, fieldErrors } (message = custom status copy when one exists).
export const useResendOtpMutation = ({ onSuccess, onError } = {}) => {
  return useAuthApiMutation({
    mutationFn: resendOtp,
    flow: "otp",
    onSuccess,
    onError,
  });
};
