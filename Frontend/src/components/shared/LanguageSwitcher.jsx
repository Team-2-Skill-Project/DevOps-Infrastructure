import { Globe } from "lucide-react";
import { useLocation, useNavigate, useParams } from "react-router-dom";

const LANGUAGES = {
  en: { label: "English", next: "العربية" },
  ar: { label: "العربية", next: "English" },
};

export default function LanguageSwitcher() {
  const { lang = "en" } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  const current = LANGUAGES[lang] || LANGUAGES.en;
  const nextLanguage = lang === "ar" ? "en" : "ar";

  const changeLanguage = () => {
    const pathWithoutLanguage = location.pathname.replace(/^\/(en|ar)/, "") || "/";
    navigate(`/${nextLanguage}${pathWithoutLanguage}${location.search}`, {
      replace: true,
    });
  };

  return (
    <button
      type="button"
      onClick={changeLanguage}
      className="inline-flex items-center gap-1.5 text-sm text-slate-600 transition-colors hover:text-slate-900"
      aria-label={`Switch language to ${current.next}`}
    >
      <Globe className="h-4 w-4" />
      {current.label}
    </button>
  );
}
