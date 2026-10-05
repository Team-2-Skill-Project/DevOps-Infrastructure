import { Download, RefreshCw } from "lucide-react";
import { useTranslation } from "react-i18next";
import { Button } from "@/components/ui/button";

export default function CompaniesPageHeader({ onExport, onRefresh }) {
  const { t } = useTranslation("dashboard");

  return (
    <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 className="text-[22px] font-bold tracking-tight text-ink">
          {t("pages.admin.companies.title")}
        </h1>
        <p className="mt-1 text-[14px] text-muted">
          {t("pages.admin.companies.subtitle")}
        </p>
      </div>
      <div className="flex flex-wrap gap-2.5">
        <Button
          type="button"
          variant="outline"
          onClick={onExport}
          className="h-10 gap-2 rounded-xl border-border bg-surface text-[13.5px] font-semibold text-ink hover:bg-background"
        >
          <Download className="h-4 w-4" />
          Export
        </Button>
        <Button
          type="button"
          onClick={onRefresh}
          className="h-10 gap-2 rounded-xl bg-primary text-[13.5px] font-semibold text-primary-foreground hover:bg-primary/90"
        >
          <RefreshCw className="h-4 w-4" />
          Refresh
        </Button>
      </div>
    </div>
  );
}
