import { useAuthApiMutation } from "@/features/auth/hooks/useAuthApiMutation";
import { verifyOtp } from "@/features/auth/api/auth.api";

// Sends { email, code } to confirm the 6-digit code; resolves with { token }, rejects with { status, message, fieldErrors } (message = custom status copy when one exists).
export const useVerifyOtpMutation = ({ onSuccess, onError } = {}) => {
  return useAuthApiMutation({
    mutationFn: verifyOtp,
    flow: "otp",
    onSuccess,
    onError,
  });
};
