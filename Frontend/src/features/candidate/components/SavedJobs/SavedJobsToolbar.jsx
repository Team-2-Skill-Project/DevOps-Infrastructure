import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

export default function SavedJobsToolbar({ jobsCount }) {
  const { t } = useTranslation("common");
  return (
    <div className="flex flex-wrap items-center justify-between gap-3 sm:gap-4 mb-6 bg-surface border border-border p-3 sm:p-4 rounded-xl shadow-xs">
      <div className="flex w-full sm:w-auto items-center gap-2 overflow-x-auto pb-1 md:pb-0">
        <Button className="bg-secondary text-secondary-foreground text-xs font-semibold px-3.5 py-2 rounded-lg">
          <span>{t("ui.savedJobs.allSaved")}</span>
          <span className="ms-1.5 bg-surface/20 text-[10px] px-1.5 py-0.5 rounded-full">
            {jobsCount}
          </span>
        </Button>
        {["ui.savedJobs.highFit", "ui.savedJobs.freshAdded", "ui.savedJobs.applied"].map((key) => (
          <Button
            key={key}
            className="bg-background text-primary border border-border text-xs font-semibold px-3.5 py-2 rounded-lg"
          >
            {t(key)}
          </Button>
        ))}
      </div>
      <Button className="w-full lg:w-auto text-xs font-semibold text-primary border border-border bg-background px-3.5 py-2 rounded-lg">
        {t("ui.savedJobs.sortBestMatch")}
      </Button>
    </div>
  );
}
