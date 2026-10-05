import { BriefcaseBusiness } from "lucide-react";
import { useTranslation } from "react-i18next";
import { EmptyState } from "@/features/admin/shared";

/**
 * JobManagementEmpty
 * Empty-state view shown when no jobs exist on the platform at all.
 */
export default function JobManagementEmpty() {
  const { t } = useTranslation("dashboard");
  return (
    <EmptyState
      icon={<BriefcaseBusiness className="w-7 h-7" />}
      title={t("pages.admin.jobManagementStates.empty")}
      description="Jobs will appear here once companies post internally or external sources start syncing listings."
    />
  );
}