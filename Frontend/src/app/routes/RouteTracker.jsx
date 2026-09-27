import { saveLastRoute } from "@/components/shared/i18n/languageStorage";
import { useEffect } from "react";
import { useParams } from "react-router-dom";

export default function RouteTracker() {
  const { lang } = useParams();

  useEffect(() => {
    if (!lang) return;

    saveLastRoute(lang);
  }, [lang]);

  return null;
}
