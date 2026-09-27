import React from 'react'
import { Link } from 'react-router-dom'
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";

export default function LoginFooter() {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("common");

  return (
    <>
            <p className="text-[#64748b] -mt-3">
              {t("auth.login.terms")}{" "}
              <Link className="underline">{t("auth.login.termsLink")}</Link>{" "}
              {t("auth.login.privacyLink")}.
            </p>
            <p className="text-[#64748b] mt-3">
              {t("auth.login.noAccount")}{" "}
              <Link
                to={localizedPath("/auth/register")}
                className="text-[#2563eb] font-semibold hover:underline"
              >
                {t("auth.login.create")}
              </Link>
            </p>
          </>
  )
}
