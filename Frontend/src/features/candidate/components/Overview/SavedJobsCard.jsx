import { Link } from "react-router-dom";
import { Bookmark } from "lucide-react";
import { cn } from "@/lib/utils";
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";

/**
 * @typedef {Object} SavedJobItem
 * @property {string} id
 * @property {string} title
 * @property {string} company
 */

/**
 * Saved Jobs summary card for the candidate dashboard.
 * Uses only MatchIn @theme tokens.
 *
 * @param {{
 *   jobs?: SavedJobItem[],
 *   isPartial?: boolean,
 *   className?: string,
 * }} props
 */
export default function SavedJobsCard({
  jobs = DEFAULT_SAVED_JOBS,
  isPartial = false,
  className,
}) {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("dashboard");
  const isEmpty = isPartial || jobs.length === 0;

  return (
    <div
      id="saved-jobs-card"
      className={cn(
        "rounded-2xl border border-border bg-surface p-6 shadow-[0_2px_8px_rgba(0,0,0,0.02)]",
        className
      )}
    >
      <div className="mb-3 flex items-center justify-between">
        <h2 className="text-[16px] font-bold text-primary">{t("overview.savedJobsCard.title")}</h2>
        <Link
          to={localizedPath("/dashboard/saved-jobs")}
          className="text-[12px] font-semibold text-primary hover:underline"
        >
          {t("overview.savedJobsCard.view")}
        </Link>
      </div>

      {isEmpty ? (
        <p className="text-[12.5px] text-muted">{t("overview.savedJobsCard.empty")}</p>
      ) : (
        <div className="space-y-2">
          {jobs.map((job) => (
            <div
              key={job.id}
              className="flex items-center gap-2.5 text-[13px] text-ink"
            >
              <Bookmark className="h-4 w-4 shrink-0 text-muted" />
              <span className="truncate">
                {job.title} — {job.company}
              </span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

const DEFAULT_SAVED_JOBS = [
  { id: "1", title: "Full Stack Engineer", company: "FinEdge" },
  { id: "2", title: "Frontend Infra Eng.", company: "CloudScale" },
];
