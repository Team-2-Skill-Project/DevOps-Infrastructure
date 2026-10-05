import React from "react";
import { ShieldCheck } from "lucide-react";
import { useTranslation } from "react-i18next";

export default function SecurityNotice({ text, className = "" }) {
  const { t } = useTranslation("common");
  return (
    <div
      className={`mt-6 flex items-start gap-2.5 rounded-xl border border-gray-100 bg-gray-50/80 p-3 text-start text-xs text-gray-500 leading-normal ${className}`}
    >
      <div className="rounded-md border border-gray-200 bg-white p-1 text-primary">
        <ShieldCheck className="h-4 w-4 shrink-0" />
      </div>
      <p className="flex-1 self-center text-xs text-gray-500">
        {text || t("auth.reset.security")}
      </p>
    </div>
  );
}
