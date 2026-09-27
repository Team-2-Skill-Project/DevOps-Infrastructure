import { useState } from "react";
import { CheckCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { MOCK_NOTIFICATIONS } from "@/constants/notificationsMock";
import NotificationsList from "../components/NotificationsPage/NotificationsList";

export default function NotificationsPage() {
  const [notifications, setNotifications] = useState(MOCK_NOTIFICATIONS);
  const unreadCount = notifications.filter((n) => !n.read).length;

  const markAsRead = (id) => {
    setNotifications((prev) =>
      prev.map((n) => (n.id === id ? { ...n, read: true } : n)),
    );
  };

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((n) => ({ ...n, read: true })));
  };

  return (
    <main className="mx-auto max-w-[720px] px-5 py-8">
      <div className="mb-5 flex items-center justify-between">
        <div>
          <h1 className="font-display text-[22px] font-bold text-ink">
            Notifications
          </h1>
          <p className="mt-1 text-[12px] text-muted">
            {unreadCount > 0 ? `${unreadCount} unread` : "You're all caught up"}
          </p>
        </div>
        <Button
          type="button"
          variant="outline"
          onClick={markAllAsRead}
          disabled={unreadCount === 0}
          className="h-9 gap-1.5 rounded-lg border-border px-3.5 text-[12px] font-semibold text-ink hover:bg-background disabled:opacity-50"
        >
          <CheckCheck className="h-4 w-4" />
          Mark all as read
        </Button>
      </div>

      <NotificationsList
        notifications={notifications}
        onMarkAsRead={markAsRead}
      />
    </main>
  );
}
