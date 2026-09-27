import React from "react";
import { ArrowLeft } from "lucide-react";
import { Link } from "react-router-dom";
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";
import LanguageSwitcher from "@/components/shared/LanguageSwitcher";

export default function AuthHeader({
  backTo = "/auth/login",
  backLabel,
  bordered = true,
  className = "",
}) {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("common");

  return (
    <header
      className={`flex items-center justify-between px-6 py-4 sm:px-8 ${
        bordered ? "border-b border-slate-200 bg-stone-50" : ""
      } ${className}`}
    >
      <Link
        to={localizedPath(backTo)}
        className="flex items-center gap-2 text-sm font-semibold text-slate-900 transition-colors hover:text-primary"
      >
        <ArrowLeft className="h-3.5 w-3.5" />
        {backLabel || t("auth.reset.back", { defaultValue: "Back to sign in" })}
      </Link>

      <LanguageSwitcher />
    </header>
  );
}
