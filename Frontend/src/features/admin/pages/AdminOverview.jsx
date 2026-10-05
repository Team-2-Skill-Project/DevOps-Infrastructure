import { useState, useEffect } from "react";
import { motion } from "framer-motion";
import {
  AdminDashboardSkeleton,
  DashboardHeader,
  StatCard,
  QuickActions,
  RecentActivity,
  AuditLogs,
  containerVariants,
  overviewStats,
  quickActions,
  recentActivity,
  auditLogs,
} from "@/features/admin/components/adminDashboard";


export default function AdminDashboard({ isLoading: isLoadingProp }) {
  const [isLoading, setIsLoading] = useState(isLoadingProp ?? false);

  // Keep internal state in sync when the parent controls `isLoading`.
  useEffect(() => {
    if (isLoadingProp !== undefined) {
      setIsLoading(isLoadingProp);
    }
  }, [isLoadingProp]);

  if (isLoading) {
    return <AdminDashboardSkeleton />;
  }

  const handleRefresh = () => {
    // TODO: wire to data re-fetch
  };

  const handleNewCompany = () => {
    // TODO: open create-company flow
  };

  return (
    <div className="min-h-screen bg-background text-[#222831] px-4 py-8 md:px-7 md:py-12 font-sans antialiased">
      <div className="max-w-7xl mx-auto space-y-7">
        {/* ========== PAGE HEADER ========== */}
        <DashboardHeader
          onRefresh={handleRefresh}
          onNewCompany={handleNewCompany}
        />

        {/* ========== OVERVIEW STATISTICS ========== */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="grid grid-cols-1 min-[440px]:grid-cols-2 min-[700px]:grid-cols-3 min-[1100px]:grid-cols-5 gap-4"
        >
          {overviewStats.map((stat) => (
            <StatCard key={stat.id} {...stat} />
          ))}
        </motion.div>

        {/* ========== QUICK ACTIONS ========== */}
        <QuickActions actions={quickActions} />

        {/* ========== RECENT ACTIVITY + AUDIT LOGS ========== */}
        <div className="grid grid-cols-1 min-[900px]:grid-cols-2 gap-5">
          <RecentActivity items={recentActivity} />
          <AuditLogs items={auditLogs} />
        </div>
      </div>
    </div>
  );
}