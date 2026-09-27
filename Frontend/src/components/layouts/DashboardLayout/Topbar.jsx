import NotificationsBellDropdown from "@/features/candidate/components/NotificationsPage/NotificationsBellDropdown";
import { Menu, Search, User } from "lucide-react";
import { useLocation, useNavigate, useParams } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import LanguageToggle from "@/components/shared/LanguageToggle";

export default function Topbar({ onOpenMobileSidebar, userName, userRole }) {
  const location = useLocation();
  const navigate = useNavigate();
  const { lang } = useParams();

  const handleLanguageChange = (newLang) => {
    const segments = location.pathname.split("/");
    segments[1] = newLang;
    navigate(segments.join("/") + location.search + location.hash);
  };

  return (
    <header className="sticky top-0 z-40 border-b border-border/60 bg-background/80 px-6 py-4 backdrop-blur-md">
      <div className="mx-auto flex max-w-[1360px] items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <Button
            type="button"
            variant="outline"
            size="icon"
            onClick={onOpenMobileSidebar}
            className="rounded-xl border-border bg-surface p-2 text-muted md:hidden"
          >
            <Menu className="h-[22px] w-[22px]" />
          </Button>

          <div
            className={cn(
              "hidden w-64 items-center rounded-xl border border-border bg-surface px-3.5 py-2 shadow-sm transition-all",
              "focus-within:border-primary focus-within:ring-1 focus-within:ring-primary",
              "sm:flex lg:w-80",
            )}
          >
            <Search className="me-2 h-[18px] w-[18px] shrink-0 text-muted/70" />
            <Input
              type="text"
              placeholder="Search roles, skills..."
              className="h-auto w-full border-0 bg-transparent p-0 text-[13px] text-ink shadow-none placeholder:text-muted/70 focus-visible:ring-0"
            />
            <kbd className="rounded border border-border bg-background px-1.5 py-0.5 font-mono text-[11px] text-muted">
              ⌘S
            </kbd>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <LanguageToggle language={lang} onChange={handleLanguageChange} />
          <NotificationsBellDropdown />
          <div className="flex items-center gap-3 border-s border-border ps-2">
            <div className="relative flex items-center justify-center">
              <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-sm">
                <User className="h-[19px] w-[19px]" />
              </div>
              <span className="absolute bottom-0 end-0 h-2.5 w-2.5 rounded-full bg-success ring-2 ring-surface" />
            </div>
            <div className="hidden flex-col text-start sm:flex">
              <span className="text-[13px] font-semibold leading-tight text-primary">
                {userName}
              </span>
              <span className="text-[11px] leading-none text-muted">
                {userRole}
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
