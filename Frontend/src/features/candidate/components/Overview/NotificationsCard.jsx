import { Link } from "react-router-dom";
import { cn } from "@/lib/utils";
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";

/**
 * @typedef {Object} NotificationItem
 * @property {string} id
 * @property {string} message
 */

/**
 * Notifications summary card for the candidate dashboard.
 * Uses only MatchIn @theme tokens.
 *
 * @param {{
 *   notifications?: NotificationItem[],
 *   className?: string,
 * }} props
 */
export default function NotificationsCard({
  notifications = DEFAULT_NOTIFICATIONS,
  className,
}) {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("dashboard");

  return (
    <div
      className={cn(
        "rounded-2xl border border-border bg-surface p-6 shadow-[0_2px_8px_rgba(0,0,0,0.02)]",
        className
      )}
    >
      <div className="mb-3 flex items-center justify-between">
        <h2 className="text-[16px] font-bold text-primary">{t("overview.notifications.title")}</h2>
        <Link
          to={localizedPath("/dashboard/notifications")}
          className="text-[12px] font-semibold text-primary hover:underline"
        >
          {t("overview.notifications.view")}
        </Link>
      </div>

      {notifications.length === 0 ? (
        <p className="text-[12.5px] text-muted">{t("overview.notifications.empty")}</p>
      ) : (
        <div className="space-y-2.5">
          {notifications.map((item) => (
            <div
              key={item.id}
              className="flex items-start gap-2 text-[12.5px] leading-snug text-muted"
            >
              <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
              {item.message}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

const DEFAULT_NOTIFICATIONS = [
  {
    id: "1",
    message: "New match: Lead Frontend Architect at Stripe",
  },
  {
    id: "2",
    message: "Vercel application moved to In Review",
  },
];
