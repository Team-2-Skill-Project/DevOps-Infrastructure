import { useTranslation } from "react-i18next";
import { Briefcase, History, FileText } from "lucide-react";

import { Button } from "@/components/ui/button";
import Section from "./Section";

const QUICK_LINKS = [
  { key: "jobs", icon: Briefcase },
  { key: "activity", icon: History },
  { key: "auditLogs", icon: FileText },
];

/**
 * CompanyQuickLinks
 * Cross-links to the company's jobs, activity trail and audit logs.
 */
export default function CompanyQuickLinks({ onNavigate }) {
  const { t } = useTranslation("dashboard");

  return (
    <Section title={t("pages.admin.companyDetails.quickLinks")}>
      <div className="flex flex-col gap-2">
        {QUICK_LINKS.map(({ key, icon: Icon }) => (
          <Button
            key={key}
            variant="outline"
            onClick={() => onNavigate?.(key)}
            className="h-10 w-full justify-center gap-2 rounded-xl border-border bg-surface text-ink"
          >
            <Icon className="h-4 w-4" />
            {t(`pages.admin.companyDetails.links.${key}`)}
          </Button>
        ))}
      </div>
    </Section>
  );
}