import { api } from "@/services/axios/axiosInstance";



// POST /api/auth/register
export const registerRequest = (payload) =>
  api.post("/api/auth/register", payload).then((res) => res.data);

// POST /api/auth/verify-email-otp — send the code as `otp`; rejects with the axios error.
export const verifyEmailOtpRequest = async (payload) => {
    const { data } = await api.post("/api/auth/verify-email-otp", { ...payload, otp: payload.code })
    return data;
}

// POST /api/auth/resend-email-otp — resolves with the normalized server payload, rejects with the axios error.
export const resendEmailOtpRequest = (payload) =>
  api.post("/api/auth/resend-email-otp", payload).then((res) => res.data);

// POST /api/auth/login — resolves with the server payload (access_token + user), rejects with the axios error.
export const loginRequest = (payload) =>
  api.post("/api/auth/login", payload).then((res) => res.data);

// GET /api/auth/me — resolves with the authenticated user, rejects with the axios error when the token is absent/expired.
export const meRequest = () =>
  api.get("/api/auth/me").then((res) => res.data?.data ?? res.data);

// Posts { email } to the reset endpoint; resolves with the normalized server payload, rejects with the axios error.
export const forgotPassword = async (payload) => {
    const { data } = await api.post("/api/auth/forgot-password", payload)
    return data;
}

// Posts { email, code } to the OTP endpoint; resolves with { token } for the next step, rejects with the axios error.
export const verifyOtp = async (payload) => {
    const { data } = await api.post("/api/auth/verify-email-otp", { ...payload, otp: payload.code })
    // Normalize the raw server shape here so no hook/Redux ever stores it (convention §4.5).
    const body = data?.data ?? data;
    // The next step (reset-password) sends this back as `reset_token`, so accept that key first.
    return { token: body?.reset_token ?? body?.token ?? body?.access_token ?? null };
}
// Posts { email, code } to the recovery OTP endpoint; resolves with { token } for the next step, rejects with the axios error.
export const verifyForgotOtp = async (payload) => {
    // The server expects the code as `otp`, not `code` — rename here so the wire format lives in one place.
    const { data } = await api.post("/api/auth/forgot-password/verify-otp", { ...payload, otp: payload.code })
    // Normalize the raw server shape here so no hook/Redux ever stores it (convention §4.5).
    const body = data?.data ?? data;
    // The next step (reset-password) sends this back as `reset_token`, so accept that key first.
    return { token: body?.reset_token ?? body?.token ?? body?.access_token ?? null };
}

// Posts { email } to the OTP resend endpoint; resolves with the normalized server payload, rejects with the axios error.
export const resendOtp = async (payload) => {
    const { data } = await api.post("/api/auth/resend-email-otp", payload)
    return data;
}

// Posts { email, token, password, password_confirmation } to the reset endpoint; resolves with the normalized server payload, rejects with the axios error.
export const resetPassword = async (payload) => {
    const { data } = await api.post("/api/auth/reset-password", { ...payload, reset_token: payload.token })
    return data;
}
