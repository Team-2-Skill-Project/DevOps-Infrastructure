import { NavLink } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { PanelLeftClose, PanelLeftOpen, Sparkles } from "lucide-react";
import LogoNavy from "@/assets/logo/MatchIn_logo.svg";
import LogoText from "@/assets/logo/logo_text.svg";
import { cn } from "@/lib/utils";
import { buildSidebarNav } from "@/utils/buildSidebarNav";
import { dashboard } from "@/app/routes/dashboard.routes";
import { useLocalizedPath } from "@/utils/routes";


function NavItem({ icon: Icon, label, labelKey, href, end, collapsed, onNavigate, t }) {
  return (
    <NavLink
      to={href}
      end={end}
      onClick={onNavigate}
      className={({ isActive }) =>
        cn(
          "group relative flex items-center gap-3 rounded-xl px-3.5 py-3 text-[13px] font-medium transition-all",
          collapsed && "justify-center px-0",
          isActive
            ? "bg-primary font-semibold text-primary-foreground shadow-sm"
            : "text-muted hover:bg-background hover:text-primary",
        )
      }
    >
      <Icon className="h-5 w-5 shrink-0" />
      {!collapsed && <span>{t(labelKey, label)}</span>}

      {/* Tooltip shown only in collapsed mode, on hover */}
      {collapsed && (
        <span className="pointer-events-none absolute start-[70px] z-[100] whitespace-nowrap rounded-lg bg-primary px-2.5 py-1 text-xs text-primary-foreground opacity-0 shadow-lg transition-opacity group-hover:opacity-100">
          {t(labelKey, label)}
        </span>
      )}
    </NavLink>
  );
}

/**
 * Sidebar
 * ---------------------------------------------------------------------------
 * Fixed, collapsible sidebar. State (collapsed / mobile drawer) lives in
 * DashboardLayout and is passed down as props so the layout stays the single
 * source of truth for the shell's responsive behavior. Nav items themselves
 * come from the dashboard route config (see NAV_ITEMS above), not a
 * hand-maintained list.
 */
export default function Sidebar({
  collapsed,
  onToggleCollapse,
  mobileOpen,
  onCloseMobile,
}) {
  const { t } = useTranslation("dashboard");
  const localizedPath = useLocalizedPath();
  const navItems = buildSidebarNav(dashboard, localizedPath("/dashboard"));

  return (
    <>
      {mobileOpen && (
        <div
          onClick={onCloseMobile}
          className="fixed inset-0 z-40 bg-primary/40 backdrop-blur-sm md:hidden"
        />
      )}

      <aside
        className={cn(
          "fixed inset-y-0 start-0 z-50 h-screen p-0 transition-all duration-300 md:p-4",
          collapsed ? "w-24" : "w-72",
          mobileOpen ? "block" : "hidden md:block",
        )}
      >
        <div className="flex h-full flex-col justify-between rounded-none border border-border/80 bg-surface/90 p-4 shadow-sm backdrop-blur-md transition-all duration-300 md:rounded-2xl">
          <div>
            {/* Logo & Brand */}
            <div
              className={cn(
                "mb-4 border-b border-border/50 pb-5",
                collapsed
                  ? "flex flex-col items-center gap-2"
                  : "flex items-center gap-2",
              )}
            >
              <NavLink
                to={localizedPath("/dashboard")}
                end
                onClick={onCloseMobile}
                className={cn(
                  "flex min-w-0 items-center gap-3",
                  !collapsed && "flex-1",
                  collapsed && "justify-center",
                )}
              >
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary shadow-sm">
                  <img src={LogoNavy} alt="MatchIn Logo" />
                </div>
                {!collapsed && (
                  <div className="flex min-w-0 flex-col">
                    <div className="h-10 w-auto">
                      <img src={LogoText} alt="MatchIn Logo" />
                    </div>

                    <span className="mt-1 text-center text-[10px] font-medium tracking-wide text-muted">
                      Career Guidance
                    </span>
                  </div>
                )}
              </NavLink>

              <button
                type="button"
                onClick={onToggleCollapse}
                aria-label={collapsed ? t("actions.expandSidebar") : t("actions.collapseSidebar")}
                title={collapsed ? t("actions.expandSidebar") : t("actions.collapseSidebar")}
                className={cn(
                  "flex shrink-0 items-center justify-center rounded-lg p-1.5 text-muted transition-colors hover:text-primary",
                )}
              >
                {collapsed ? <PanelLeftOpen className="h-5 w-5" /> : <PanelLeftClose className="h-5 w-5" />}
              </button>
            </div>

            {/* Navigation */}
            <nav className="space-y-1.5">
              {!collapsed && (
                <div className="px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-muted/70">
                  {t("navigation.mainMenu")}
                </div>
              )}
              {navItems.map((item) => (
                <NavItem
                  key={item.href}
                  {...item}
                  collapsed={collapsed}
                  onNavigate={onCloseMobile}
                  t={t}
                />
              ))}
            </nav>
          </div>

          <div className="flex flex-col gap-3">
            {/* AI Mentor callout */}
            {!collapsed && (
              <div className="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary to-primary/80 p-4 text-xs text-primary-foreground shadow-sm">
                <Sparkles className="mb-2 h-6 w-6 text-accent" />
                <p className="mb-1 text-[13px] font-bold">{t("mentor.title")}</p>
                <p className="mb-3 text-[11px] text-primary-foreground/70">
                  {t("mentor.description")}
                </p>
                {/* TODO: point to the real AI mentor route */}
                <a
                  href="#"
                  className="block rounded-lg bg-surface px-3 py-2 text-center text-[11px] font-bold text-primary shadow-sm transition-colors hover:bg-background"
                >
                  {t("mentor.action")}
                </a>
              </div>
            )}

          </div>
        </div>
      </aside>
    </>
  );
}
