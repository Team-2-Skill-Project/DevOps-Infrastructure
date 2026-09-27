import { useEffect } from "react";
import { useParams } from "react-router-dom";
import i18n from "./index";

const SUPPORTED_LANGUAGES = ["en", "ar"];

export default function I18nSync() {
  const { lang } = useParams();

  useEffect(() => {
    if (!SUPPORTED_LANGUAGES.includes(lang)) return;

    i18n.changeLanguage(lang);

    document.documentElement.lang = lang;

    document.documentElement.dir = lang === "ar" ? "rtl" : "ltr";
  }, [lang]);

  return null;
}
