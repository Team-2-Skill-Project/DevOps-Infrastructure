import {
  SkeletonLoader,
  SkeletonHeader,
  SkeletonCard,
} from "@/features/admin/shared";

/**
 * AuditLogDetailsSkeleton
 * Loading skeleton mirroring the details page layout:
 * breadcrumb, header, banner, status strip and section cards.
 */
export default function AuditLogDetailsSkeleton() {
  return (
    <div className="max-w-225 mx-auto p-4 md:p-8 bg-background min-h-screen font-sans text-[#222831]">
      {/* Breadcrumb skeleton */}
      <SkeletonLoader className="h-4 w-32 mb-4" />

      {/* Header skeleton */}
      <SkeletonHeader
        lines={["h-7 w-48", "h-4 w-24"]}
        actions={[{ className: "h-9 w-36" }]}
      />

      {/* Read-only banner + status strip skeletons */}
      <SkeletonLoader className="h-12 w-full rounded-xl mb-5" />
      <SkeletonLoader className="h-14 w-full rounded-2xl mb-5" />

      {/* Section cards skeleton */}
      <SkeletonCard count={4} />
    </div>
  );
}
