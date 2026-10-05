import { Skeleton } from "@/components/ui/skeleton";
import * as React from "react";


function SkeletonRow() {
  return (
    <tr>
      <td className="px-4 py-3.5">
        <div className="flex items-center gap-3">
          <Skeleton className="h-10 w-10 shrink-0 rounded-full" />
          <div className="flex flex-col gap-1.5">
            <Skeleton className="h-3.5 w-28" />
            <Skeleton className="h-3 w-16" />
          </div>
        </div>
      </td>
      <td className="px-4 py-3.5"><Skeleton className="h-3.5 w-36" /></td>
      <td className="px-4 py-3.5"><Skeleton className="h-6 w-20 rounded-full" /></td>
      <td className="px-4 py-3.5"><Skeleton className="h-6 w-20 rounded-full" /></td>
      <td className="px-4 py-3.5">
        <div className="flex flex-col gap-1.5">
          <Skeleton className="h-3.5 w-20" />
          <Skeleton className="h-3 w-14" />
        </div>
      </td>
      <td className="px-4 py-3.5">
        <div className="flex flex-col gap-1.5">
          <Skeleton className="h-3.5 w-20" />
          <Skeleton className="h-3 w-14" />
        </div>
      </td>
      <td className="px-4 py-3.5">
        <div className="flex justify-end gap-1.5">
          <Skeleton className="h-8 w-8 rounded-lg" />
          <Skeleton className="h-8 w-8 rounded-lg" />
          <Skeleton className="h-8 w-8 rounded-lg" />
        </div>
      </td>
    </tr>
  );
}

/**
 * UsersTableSkeleton
 * rows — how many placeholder rows to render (default 8)
 */
export function UsersTableSkeleton({ rows = 8 }) {
  return (
    <tbody>
      {Array.from({ length: rows }).map((_, i) => (
        <SkeletonRow key={i} />
      ))}
    </tbody>
  );
}
