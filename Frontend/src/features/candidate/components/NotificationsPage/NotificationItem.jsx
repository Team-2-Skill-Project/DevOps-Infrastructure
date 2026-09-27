import { Button } from "@/components/ui/button";
import { NOTIFICATION_TYPE_STYLES } from "@/constants/notificationTypeStyles";

export default function NotificationItem({ notification, onMarkAsRead, compact = false }) {
  const style = NOTIFICATION_TYPE_STYLES[notification.type] ?? NOTIFICATION_TYPE_STYLES.job;
  const Icon = style.icon;

  return (
    <div className="flex gap-3 p-4">
      <div
        className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${style.iconClass}`}
      >
        <Icon className="h-[18px] w-[18px]" />
      </div>

      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <span
            className={`text-[13px] font-semibold ${notification.read ? "text-muted" : "text-ink"}`}
          >
            {notification.title}
          </span>
          {!notification.read && (
            <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-secondary" />
          )}
        </div>
        <p className={`mt-0.5 text-[12px] text-muted ${compact ? "line-clamp-1" : ""}`}>
          {notification.description}
        </p>
        <div className="mt-1 text-[11px] text-muted">{notification.time}</div>
      </div>

      {!notification.read && !compact && (
        <Button
          type="button"
          variant="ghost"
          size="sm"
          onClick={() => onMarkAsRead(notification.id)}
          className="h-auto shrink-0 self-start p-0 text-[11px] font-semibold text-primary hover:bg-transparent hover:text-primary/80"
        >
          Mark as read
        </Button>
      )}
    </div>
  );
}
