import { useTranslation } from "react-i18next";
import { Button } from "@/components/ui/button";
import Section from "./Section";
import { ACTIVITY_DOTS } from "../../shared/companiesDetailsData";

/**
 * CompanyActivity
 * Chronological activity feed with a coloured dot per entry tone.
 */
export default function CompanyActivity({ activity, onViewAll }) {
  const { t } = useTranslation("dashboard");

  return (
    <Section
      title={t("pages.admin.companyDetails.activity")}
      action={
        <Button
          variant="ghost"
          size="sm"
          onClick={onViewAll}
          className="h-8 text-xs text-muted hover:text-ink"
        >
          {t("pages.admin.companyDetails.viewAll")}
        </Button>
      }
      bodyClassName="px-5 py-1"
    >
      <ul className="divide-y divide-border">
        {activity.map((entry) => (
          <li key={entry.id} className="flex gap-3 py-3">
            <span
              className={`mt-1.5 h-2 w-2 shrink-0 rounded-full ${
                ACTIVITY_DOTS[entry.tone] ?? ACTIVITY_DOTS.muted
              }`}
            />
            <div className="min-w-0">
              <div className="text-[13.5px] font-medium text-ink">
                {entry.text}
              </div>
              <div className="mt-0.5 text-xs text-muted">{entry.meta}</div>
            </div>
          </li>
        ))}
      </ul>
    </Section>
  );
}