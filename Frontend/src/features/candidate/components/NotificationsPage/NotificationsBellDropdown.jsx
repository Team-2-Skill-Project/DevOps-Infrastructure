import { useState } from "react";
import { Bell, CheckCheck } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import NotificationsList from "./NotificationsList";
import { MOCK_NOTIFICATIONS } from "@/constants/notificationsMock";
import { useLocalizedPath } from "@/utils/routes";

const PREVIEW_LIMIT = 4;

export default function NotificationsBellDropdown() {
  const localizedPath = useLocalizedPath();
  const [notifications, setNotifications] = useState(MOCK_NOTIFICATIONS);
  const unreadCount = notifications.filter((n) => !n.read).length;
  const preview = notifications.slice(0, PREVIEW_LIMIT);

  const markAsRead = (id) => {
    setNotifications((prev) =>
      prev.map((n) => (n.id === id ? { ...n, read: true } : n)),
    );
  };

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((n) => ({ ...n, read: true })));
  };

  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          size="icon"
          className="relative h-9 w-9 shrink-0 rounded-full border-border bg-background text-primary shadow-sm hover:bg-surface"
        >
          <Bell className="h-[18px] w-[18px]" />
          {unreadCount > 0 && (
            <span className="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-secondary px-1 text-[10px] font-bold text-secondary-foreground">
              {unreadCount > 9 ? "9+" : unreadCount}
            </span>
          )}
        </Button>
      </PopoverTrigger>

      <PopoverContent align="end" className="w-80 border-border bg-surface p-0">
        <div className="flex items-center justify-between border-b border-border px-4 py-3">
          <span className="text-[13px] font-bold text-ink">Notifications</span>
          {unreadCount > 0 && (
            <Button
              type="button"
              variant="ghost"
              size="sm"
              onClick={markAllAsRead}
              className="h-auto gap-1 p-0 text-[11px] font-semibold text-primary hover:bg-transparent hover:text-primary/80"
            >
              <CheckCheck className="h-3.5 w-3.5" />
              Mark all read
            </Button>
          )}
        </div>

        <div className="max-h-96 overflow-y-auto p-2">
          <NotificationsList
            notifications={preview}
            onMarkAsRead={markAsRead}
            compact
          />
        </div>

        {/* TODO: point to the real notifications page route */}
        <Link
          to={localizedPath("/dashboard/notifications")}
          className="block border-t border-border px-4 py-2.5 text-center text-[12px] font-semibold text-primary hover:bg-background"
        >
          View all notifications
        </Link>
      </PopoverContent>
    </Popover>
  );
}
