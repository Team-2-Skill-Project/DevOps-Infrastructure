import ForgetPasswordPage from "@/features/auth/pages/ForgetPasswordPage";
import LoginPage from "@/features/auth/pages/LoginPage";
import RegisterPage from "@/features/auth/pages/RegisterPage";
import SetNewPassword from "@/features/auth/pages/SetNewPassword";
import VerifyOtpPage from "@/features/auth/pages/VerifyOtpPage";

export const auth = [
  { path: "login", element: <LoginPage /> },
  { path: "register", element: <RegisterPage /> },
  { path: "reset-password", element: <SetNewPassword /> },
  {
    path: "forgot-password",
    children: [
      { index: true, element: <ForgetPasswordPage /> },
      { path: "verify-otp", element: <VerifyOtpPage /> },
    ],
  },
];
