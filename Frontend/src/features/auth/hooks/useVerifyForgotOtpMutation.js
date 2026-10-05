import { useAuthApiMutation } from "@/features/auth/hooks/useAuthApiMutation";
import { verifyForgotOtp } from "@/features/auth/api/auth.api";

// Sends { email, code } to confirm the 6-digit code; resolves with { token }, rejects with { status, message, fieldErrors } (message = custom status copy when one exists).
export const useVerifyForgotOtpMutation = ({ onSuccess, onError } = {}) => {
  return useAuthApiMutation({
    mutationFn: verifyForgotOtp,
    flow: "otp",
    onSuccess,
    onError,
  });
};
