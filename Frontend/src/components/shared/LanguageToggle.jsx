import { useState } from "react";
import { motion } from "framer-motion";

const LANGUAGES = [
  { code: "en", label: "EN" },
  { code: "ar", label: "AR" },
];

/**
 * Controlled if `language`/`onChange` are passed (wire this to your i18n
 * setup), otherwise manages its own state so it still works standalone.
 */
export default function LanguageToggle({ language, onChange, className = "" }) {
  const [internalLang, setInternalLang] = useState("en");
  const active = language ?? internalLang;

  const handleSelect = (code) => {
    if (onChange) onChange(code);
    else setInternalLang(code);
  };

  return (
    <div
      className={`flex items-center gap-0.5 rounded-full border border-border bg-background p-0.5 ${className}`}
    >
      {LANGUAGES.map((lang) => (
        <button
          key={lang.code}
          type="button"
          onClick={() => handleSelect(lang.code)}
          aria-pressed={active === lang.code}
          className={`relative rounded-full px-2.5 py-1 text-[12px] font-semibold transition-colors ${
            active === lang.code
              ? "text-primary-foreground"
              : "text-muted hover:text-primary"
          }`}
        >
          {active === lang.code && (
            <motion.span
              layoutId="lang-switcher-pill"
              className="absolute inset-0 rounded-full bg-primary"
              transition={{ type: "spring", stiffness: 400, damping: 30 }}
            />
          )}
          <span className="relative">{lang.label}</span>
        </button>
      ))}
    </div>
  );
}
