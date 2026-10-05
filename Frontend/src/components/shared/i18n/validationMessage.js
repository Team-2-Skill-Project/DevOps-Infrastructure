import i18n from "@/components/shared/i18n";

export const validationMessage = (key, values = {}) =>
  () => i18n.t(key, { ns: "validation", ...values });

export function translateValidationMessage(message) {
  const text = String(message ?? "");
  const normalized = text.toLowerCase();
  const t = (key, values = {}) => i18n.t(key, { ns: "validation", ...values });

  if (/[\u0600-\u06ff]/.test(text)) {
    return i18n.language.startsWith("ar") ? text : t("apiGeneric");
  }
  if (/required|must be provided|cannot be blank/.test(normalized)) return t("required");
  if (/valid.*email|email.*valid/.test(normalized)) return t("emailInvalid");
  if (/at least (\d+) characters/.test(normalized)) {
    const [, count] = normalized.match(/at least (\d+) characters/);
    if (/full name|name field/.test(normalized)) return t("fullNameMin", { count });
    return /password/.test(normalized)
      ? t("passwordMin", { count })
      : t("minCharacters", { count });
  }
  if (/uppercase/.test(normalized)) return t("passwordUppercase");
  if (/lowercase/.test(normalized)) return t("passwordLowercase");
  if (/special character/.test(normalized)) return t("passwordSpecial");
  if (/password.*number|number.*password|digits only/.test(normalized)) {
    return t("passwordNumber");
  }
  if (/password.*(match|confirmation)|confirmation.*match/.test(normalized)) {
    return t("passwordMismatch");
  }
  if (/pdf|word document|file type/.test(normalized)) return t("fileType");
  if (/file.*under.*mb|file.*size/.test(normalized)) return t("fileMaxSize", { size: 10 });
  if (/choose.*file|file.*required/.test(normalized)) return t("fileRequired");

  return t("apiGeneric");
}