import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { ArrowLeft, PencilLine } from "lucide-react";

import { Button } from "@/components/ui/button";
import { InitialsAvatar } from "@/features/admin/shared";

/**
 * CompanyHeader
 * Identity block: initials avatar, company name, mono ID and the
 * back / edit action pair.
 */
export default function CompanyHeader({ company, backTo }) {
  const { t } = useTranslation("dashboard");

  return (
    <header className="mb-5 flex flex-wrap items-start justify-between gap-4">
      <div className="flex items-center gap-4">
        <InitialsAvatar
          initials={company.initials}
          colorScheme={company.colorScheme}
          className="h-14 w-14 rounded-xl text-lg"
        />
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-ink">
            {company.name}
          </h1>
          <div className="mt-0.5 font-mono text-xs text-muted">{company.id}</div>
        </div>
      </div>

      <div className="flex flex-wrap items-center gap-2.5">
        <Button
          variant="ghost"
          asChild
          className="h-10 gap-2 rounded-xl text-muted hover:text-ink"
        >
          <Link to={backTo}>
            <ArrowLeft className="h-4 w-4" />
            {t("actions.back")}
          </Link>
        </Button>
        <Button
          variant="outline"
          className="h-10 gap-2 rounded-xl border-border bg-surface text-ink"
        >
          <PencilLine className="h-4 w-4" />
          {t("pages.admin.companyDetails.editCompany")}
        </Button>
      </div>
    </header>
  );
}