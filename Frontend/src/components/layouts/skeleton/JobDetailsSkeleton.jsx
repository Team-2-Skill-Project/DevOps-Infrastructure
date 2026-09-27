export default function JobDetailsSkeleton() {
  return (
    <div>
      <div className="space-y-8">
        <div className="bg-surface border border-border rounded-2xl p-8">
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div className="flex items-start gap-5 flex-1">
              <div className="w-16 h-16 animate-pulse rounded-lg bg-border" />
              <div className="flex-1 space-y-3">
                <div className="h-3 w-40 animate-pulse rounded-lg bg-border" />
                <div className="h-8 w-72 animate-pulse rounded-lg bg-border" />
                <div className="h-3 w-56 animate-pulse rounded-lg bg-border" />
              </div>
            </div>
            <div className="space-y-2.5 md:w-70">
              <div className="h-3 w-32 animate-pulse rounded-lg bg-border" />
              <div className="h-6 w-48 animate-pulse rounded-lg bg-border" />
              <div className="h-8 w-full animate-pulse rounded-lg bg-border" />
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-7">
          <div className="lg:col-span-8 space-y-6">
            <div className="bg-surface border border-border rounded-2xl p-7 space-y-3">
              <div className="h-6 w-64 animate-pulse rounded-lg bg-border" />
              <div className="h-3 w-full animate-pulse rounded-lg bg-border" />
              <div className="h-3 w-5/6 animate-pulse rounded-lg bg-border" />
              <div className="grid grid-cols-3 gap-3 pt-3">
                <div className="h-20 animate-pulse rounded-lg bg-border" />
                <div className="h-20 animate-pulse rounded-lg bg-border" />
                <div className="h-20 animate-pulse rounded-lg bg-border" />
              </div>
            </div>
          </div>
          <div className="lg:col-span-4">
            <div className="bg-surface border border-border rounded-2xl p-6 space-y-3">
              <div className="h-4 w-36 animate-pulse rounded-lg bg-border" />
              <div className="h-7 w-40 animate-pulse rounded-lg bg-border" />
              <div className="h-10 w-full animate-pulse rounded-lg bg-border" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
