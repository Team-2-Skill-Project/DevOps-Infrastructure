import { useTranslation } from "react-i18next";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";

/**
 * OverviewStats
 * Renders the four headline counters from the mock dataset. `small` switches
 * from the large numeric style to the date style for the two date tiles.
 */
export default function OverviewStats({ stats }) {
  const { t } = useTranslation("dashboard");

  return (
    <div className="mb-5.5 grid grid-cols-2 gap-3.5 lg:grid-cols-4 max-[440px]:grid-cols-1">
      {stats.map((stat) => (
        <Card
          key={stat.label}
          className="gap-0 rounded-2xl border-border bg-surface p-4.5 shadow-[0_2px_8px_rgba(0,0,0,0.03)]"
        >
          <CardContent className="p-0">
            <div className="text-xs font-medium text-muted">
              {t(`pages.admin.companyDetails.stats.${stat.label}`)}
            </div>
            <div
              className={cn(
                "mt-1 font-bold tracking-tight text-ink",
                stat.small ? "text-base" : "text-2xl",
              )}
            >
              {stat.value}
            </div>
            <div className="mt-0.5 text-xs text-muted">{stat.sub}</div>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}