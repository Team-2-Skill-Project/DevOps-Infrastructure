import {
  SkeletonLoader,
  SkeletonHeader,
  SkeletonTable,
} from "@/features/admin/shared";

/**
 * AuditLogsListSkeleton
 * Loading skeleton mirroring the list page layout:
 * header + toolbar + data table.
 */
export default function AuditLogsListSkeleton() {
  return (
    <div className="max-w-7xl mx-auto p-4 md:p-8 bg-background min-h-screen font-sans text-[#222831]">
      {/* Header skeleton */}
      <SkeletonHeader
        lines={["h-7 w-48", "h-4 w-80", "h-3 w-40"]}
        actions={[{ className: "h-10 w-24" }, { className: "h-10 w-24" }]}
      />

      {/* Toolbar skeleton */}
      <div className="space-y-3 mb-5">
        <div className="flex flex-wrap gap-3">
          <SkeletonLoader className="flex-1 min-w-60 h-10.5 rounded-xl" />
          <SkeletonLoader className="w-36 h-10.5 rounded-xl" />
          <SkeletonLoader className="w-36 h-10.5 rounded-xl" />
          <SkeletonLoader className="w-36 h-10.5 rounded-xl" />
        </div>
      </div>

      {/* Table skeleton */}
      <SkeletonTable rows={6} />
    </div>
  );
}
