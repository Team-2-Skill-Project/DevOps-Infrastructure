import NotificationItem from "./NotificationItem";

export default function NotificationsList({ notifications, onMarkAsRead, compact = false }) {
  const today = notifications.filter((n) => n.group === "TODAY");
  const earlier = notifications.filter((n) => n.group === "EARLIER");

  return (
    <div className="flex flex-col gap-6">
      {today.length > 0 && (
        <div>
          {!compact && (
            <div className="mb-2 px-1 text-[11px] font-semibold text-muted">TODAY</div>
          )}
          <div className="divide-y divide-border overflow-hidden rounded-2xl border border-border bg-surface">
            {today.map((n) => (
              <NotificationItem
                key={n.id}
                notification={n}
                onMarkAsRead={onMarkAsRead}
                compact={compact}
              />
            ))}
          </div>
        </div>
      )}

      {earlier.length > 0 && (
        <div>
          {!compact && (
            <div className="mb-2 px-1 text-[11px] font-semibold text-muted">EARLIER</div>
          )}
          <div className="divide-y divide-border overflow-hidden rounded-2xl border border-border bg-surface">
            {earlier.map((n) => (
              <NotificationItem
                key={n.id}
                notification={n}
                onMarkAsRead={onMarkAsRead}
                compact={compact}
              />
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
