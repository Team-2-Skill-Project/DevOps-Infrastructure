import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { ChevronRight } from "lucide-react";

/**
 * CompanyBreadcrumbs
 * Breadcrumb trail. Rendered with the app's existing link + chevron pattern
 * (matching UsersDetailsPage / AuditLogsDetailsPage) instead of a separate
 * breadcrumb primitive.
 */
export default function CompanyBreadcrumbs({ companyId, backTo }) {
  const { t } = useTranslation("dashboard");

  return (
    <nav
      aria-label="Breadcrumb"
      className="mb-3.5 flex flex-wrap items-center gap-1.5 text-[13px] text-muted"
    >
      <Link to={backTo} className="transition-colors hover:text-primary">
        {t("pages.admin.companies")}
      </Link>
      <ChevronRight className="h-3.5 w-3.5 opacity-55" />
      <span className="font-mono text-muted">{companyId}</span>
    </nav>
  );
}