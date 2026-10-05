import { ErrorState } from "@/features/admin/shared";
import { useTranslation } from "react-i18next";

/**
 * JobManagementError
 * Error-state view for the job list, with a retry action.
 */
export default function JobManagementError({ onRetry }) {
  const { t } = useTranslation("dashboard");
  return (
    <ErrorState
      title={t("pages.admin.jobManagementStates.loadFailed")}
      description="Something went wrong on our end. Please try again."
      retryLabel="Retry"
      onRetry={onRetry}
    />
  );
}