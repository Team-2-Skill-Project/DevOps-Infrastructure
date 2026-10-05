import { Skeleton } from "@/components/ui/skeleton";
import * as React from "react";


export function StatsStripSkeleton({ count = 4 }) {
  return (
    <div className="mb-5.5 grid grid-cols-4 gap-3.5 max-[800px]:grid-cols-2 max-[440px]:grid-cols-1">
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} className="flex items-center gap-3.5 rounded-2xl border border-border bg-surface p-4">
          <Skeleton className="h-10 w-10 shrink-0 rounded-xl" />
          <div className="flex flex-col gap-1.5">
            <Skeleton className="h-5 w-12" />
            <Skeleton className="h-3 w-20" />
          </div>
        </div>
      ))}
    </div>
  );
}
