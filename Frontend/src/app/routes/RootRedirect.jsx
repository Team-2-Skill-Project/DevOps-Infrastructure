import { getLastRoute } from "@/components/shared/i18n/languageStorage";
import { Navigate } from "react-router-dom";
import { DEFAULT_LANGUAGE } from "@/utils/routes";

export default function RootRedirect() {
  const lastLanguage = getLastRoute();
  const language = ["en", "ar"].includes(lastLanguage)
    ? lastLanguage
    : DEFAULT_LANGUAGE;

  return <Navigate to={`/${language}`} replace />;
}
