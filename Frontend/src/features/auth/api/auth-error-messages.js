// Maps an auth API HTTP status to the i18n key of a custom message for the user.
// Pure and side-effect free (api/ stays free of i18n calls) — callers translate the key with t().
// Returns null when the server's own message is the better answer (any other 4xx, and 422 field errors).
//
// flow — "forgot" | "otp" | "reset": only statuses that mean something different per
// screen are disambiguated (404 = "no account" when requesting a link, but
// "invalid/expired code" when verifying one).

const EXPIRED_STATUSES = [401, 403, 404, 410];

export function authStatusMessageKey(status, flow = "otp") {
  if (status === 429) return "auth.errors.tooManyAttempts";
  if (status === undefined || status === null || status >= 500)
    return "auth.errors.serverUnavailable";
  if (status === 419) return "auth.errors.sessionExpired";
  if (status === 404 && flow === "forgot") return "auth.errors.noAccount";
  if (EXPIRED_STATUSES.includes(status)) return "auth.errors.invalidOrExpired";
  return null;
}

// True for statuses that mean "this code/token is no longer usable" — show the expired/reset-again view.
export function isExpiredAuthStatus(status) {
  return status === 419 || EXPIRED_STATUSES.includes(status);
}

// True for 5xx and network failures (no response → no status) — show the "something went wrong" retry view.
export function isServerErrorStatus(status) {
  return status === undefined || status === null || status >= 500;
}

// Normalized error → same shape with `message` replaced by the custom status copy
// (if one matches) plus the `messageKey` that matched. `t` is passed in, so this
// file stays i18n-free. input: { status, message, fieldErrors }.
export function applyAuthStatusMessage(error, flow, t) {
  const messageKey = authStatusMessageKey(error.status, flow);
  return messageKey ? { ...error, message: t(messageKey), messageKey } : error;
}
