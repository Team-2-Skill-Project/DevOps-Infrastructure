import { useTranslation } from "react-i18next";
import { Button } from "@/components/ui/button";
import { StatusBadge } from "@/features/admin/shared";
import Section from "./Section";

/**
 * CompanyJobs
 * Flat list of the company's postings. Each row maps onto the shared
 * `StatusBadge` (active / draft / closed) instead of repeated badge markup.
 */
export default function CompanyJobs({ jobs, onViewAll }) {
  const { t } = useTranslation("dashboard");

  return (
    <Section
      title={t("pages.admin.companyDetails.jobs")}
      action={
        <Button
          variant="ghost"
          size="sm"
          onClick={onViewAll}
          className="h-8 text-xs text-muted hover:text-ink"
        >
          {t("pages.admin.companyDetails.viewAllJobs")}
        </Button>
      }
      bodyClassName="px-5 py-1"
    >
      <ul className="divide-y divide-border">
        {jobs.map((job) => (
          <li
            key={job.id}
            className="flex items-center justify-between gap-3 py-3"
          >
            <div className="min-w-0">
              <div className="text-[13.5px] font-semibold text-ink">
                {job.title}
              </div>
              <div className="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-muted">
                {job.meta.map((item, index) => (
                  <span key={item} className="flex items-center gap-1.5">
                    {index > 0 && (
                      <span className="h-1 w-1 rounded-full bg-border" />
                    )}
                    {item}
                  </span>
                ))}
              </div>
            </div>
            <StatusBadge status={job.status} label={job.statusLabel} />
          </li>
        ))}
      </ul>
    </Section>
  );
}