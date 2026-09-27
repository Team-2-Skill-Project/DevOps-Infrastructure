import { useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";

function formatSegment(segment) {
  return decodeURIComponent(segment)
    .split("-")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

export default function DashboardBreadcrumb({ workspaceLabel = "Candidate Workspace" }) {
  const { pathname } = useLocation();
  const { t } = useTranslation("dashboard");
  const segments = pathname.split("/").filter(Boolean);
  const dashboardIndex = segments.indexOf("dashboard");
  const nextSegment = dashboardIndex !== -1 ? segments[dashboardIndex + 1] : undefined;
  const pageLabel = nextSegment ? formatSegment(nextSegment) : t("overview.page");

  return (
    <div className="mb-1 flex items-center gap-2 text-[12px] font-semibold uppercase tracking-wider text-muted">
      <span>{t("overview.workspace", { defaultValue: workspaceLabel })}</span>
      <span>/</span>
      <span className="font-bold text-primary">{pageLabel}</span>
    </div>
  );
}
