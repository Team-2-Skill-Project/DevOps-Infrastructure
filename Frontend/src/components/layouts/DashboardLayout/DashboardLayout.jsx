import { useState } from "react";
import { useSelector } from "react-redux";

import { cn } from "@/lib/utils";
import Sidebar from "./Sidebar";
import DashboardFooter from "./DashboardFooter";
import Topbar from "./Topbar";
import { Outlet } from "react-router-dom";
import { selectCurrentUser } from "@/features/auth/store/auth.selectors";

export default function DashboardLayout() {
  const [collapsed, setCollapsed] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  // const user = useSelector(selectCurrentUser);

  return (
    <div className="flex min-h-screen bg-background font-sans text-ink antialiased">
      <Sidebar
        collapsed={collapsed}
        onToggleCollapse={() => setCollapsed((v) => !v)}
        mobileOpen={mobileOpen}
        onCloseMobile={() => setMobileOpen(false)}
        role={'admin'}
      />

      <div
        className={cn(
          "flex min-w-0 flex-1 flex-col transition-all duration-300",
          collapsed ? "md:ms-24" : "md:ms-72",
        )}
      >
        <Topbar
          onOpenMobileSidebar={() => setMobileOpen(true)}
          userName={"Alex"}
          userRole={"Dev"}
        />

        <main className="w-full min-h-screen bg-background pb-12">
          <div className="mx-auto max-w-[1360px] px-6 pt-6">
            <Outlet />
          </div>
        </main>

        <DashboardFooter />
      </div>
    </div>
  );
}
