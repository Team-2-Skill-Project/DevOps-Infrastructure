import { motion } from "framer-motion";
import { Skeleton } from "@/components/ui/skeleton";

/** Shared card shell so skeletons inherit the exact card shape/border/padding. */
function SkeletonCard({ className, children }) {
  return (
    <div
      className={`bg-white border border-border rounded-2xl shadow-[0_2px_8px_rgba(0,0,0,0.03)] ${className}`}
    >
      {children}
    </div>
  );
}

/** Section heading placeholder (title + optional "View all" link). */
function SectionHeadingSkeleton({ withAction = false }) {
  return (
    <div className="flex items-center justify-between gap-3 h-6">
      <Skeleton className="h-4.5 w-40 rounded-md" />
      {withAction && <Skeleton className="h-4 w-16 rounded-md" />}
    </div>
  );
}

/** A list row placeholder matching the ListItem dimensions. */
function SkeletonListItem() {
  return (
    <div className="flex items-start gap-3.5 p-[14px_18px]">
      <Skeleton className="w-9 h-9 rounded-xl shrink-0 mt-0.5" />
      <div className="flex-1 min-w-0 space-y-2">
        <Skeleton className="h-3.5 w-3/4 rounded-md" />
        <div className="flex items-center gap-2">
          <Skeleton className="h-5 w-16 rounded-full" />
          <Skeleton className="w-0.75 h-0.75 rounded-full" />
          <Skeleton className="h-3 w-14 rounded-md" />
        </div>
      </div>
    </div>
  );
}

/**
 * Admin Dashboard loading state. Mirrors the exact layout, grid
 * responsiveness, dimensions, spacing and background of <AdminDashboard />.
 */
export default function AdminDashboardSkeleton() {
  return (
    <div className="min-h-screen bg-background text-[#222831] px-4 py-8 md:px-7 md:py-12 font-sans antialiased">
      <div className="max-w-7xl mx-auto space-y-7">
        {/* ========== PAGE HEADER ========== */}
        <motion.header
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4 }}
          className="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div className="space-y-2">
            <Skeleton className="h-7 w-48 rounded-md" />
            <Skeleton className="h-4 w-64 rounded-md" />
          </div>
          <div className="flex items-center gap-2.5 flex-wrap">
            <Skeleton className="h-10 w-28 rounded-xl" />
            <Skeleton className="h-10 w-36 rounded-xl" />
          </div>
        </motion.header>

        {/* ========== OVERVIEW STATISTICS ========== */}
        <div className="grid grid-cols-1 min-[440px]:grid-cols-2 min-[700px]:grid-cols-3 min-[1100px]:grid-cols-5 gap-4">
          {[...Array(5)].map((_, index) => (
            <SkeletonCard
              key={index}
              className="p-[20px_18px] flex flex-col justify-between gap-3"
            >
              <div className="flex items-center justify-between">
                <Skeleton className="w-10 h-10 rounded-xl" />
                <Skeleton className="h-5 w-14 rounded-full" />
              </div>
              <div className="space-y-1.5">
                <Skeleton className="h-7 w-20 rounded-md" />
                <Skeleton className="h-3.5 w-24 rounded-md" />
                <Skeleton className="h-3 w-32 rounded-md" />
              </div>
            </SkeletonCard>
          ))}
        </div>

        {/* ========== QUICK ACTIONS ========== */}
        <section className="space-y-3.5">
          <SectionHeadingSkeleton />
          <div className="grid grid-cols-1 min-[480px]:grid-cols-2 min-[900px]:grid-cols-4 gap-3.5">
            {[...Array(4)].map((_, index) => (
              <SkeletonCard
                key={index}
                className="p-[20px_18px] flex flex-col gap-3.5"
              >
                <Skeleton className="w-11 h-11 rounded-xl" />
                <div className="space-y-1.5">
                  <Skeleton className="h-4 w-32 rounded-md" />
                  <Skeleton className="h-3 w-40 rounded-md" />
                </div>
                <Skeleton className="mt-auto h-3.5 w-14 rounded-md" />
              </SkeletonCard>
            ))}
          </div>
        </section>

        {/* ========== RECENT ACTIVITY + AUDIT LOGS ========== */}
        <div className="grid grid-cols-1 min-[900px]:grid-cols-2 gap-5">
          {[...Array(2)].map((_, columnIndex) => (
            <section key={columnIndex} className="space-y-3.5">
              <SectionHeadingSkeleton withAction />
              <SkeletonCard className="overflow-hidden">
                <div className="divide-y divide-border">
                  {[...Array(5)].map((_, rowIndex) => (
                    <SkeletonListItem key={rowIndex} />
                  ))}
                </div>
              </SkeletonCard>
            </section>
          ))}
        </div>
      </div>
    </div>
  );
}
