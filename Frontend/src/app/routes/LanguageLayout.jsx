import { Navigate, Outlet, useParams } from "react-router-dom";
import RouteTracker from "./RouteTracker";
import I18nSync from "@/components/shared/i18n/I18nSync";

const SUPPORTED_LANGUAGES = ["en", "ar"];

export default function LanguageLayout() {
  const { lang } = useParams();

  if (!SUPPORTED_LANGUAGES.includes(lang)) {
    return <Navigate to="/en" replace />;
  }

  return (
    <>
      <RouteTracker />
      <I18nSync />
      <Outlet />
    </>
  );
}
