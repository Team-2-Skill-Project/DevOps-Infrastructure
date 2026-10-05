/**
 * JobManagementSkeleton
 * Loading skeleton mirroring the toolbar + job row layout.
 */
export default function JobManagementSkeleton() {
  return (
    <div className="space-y-4">
      <div className="flex gap-2.5 mb-5">
        <div className="h-11 flex-1 rounded-full bg-gradient-to-r from-[#EFEDE8] via-[#F5F3EF] to-[#EFEDE8] bg-[length:400%_100%] animate-pulse" />
        <div className="h-11 w-40 rounded-full bg-gradient-to-r from-[#EFEDE8] via-[#F5F3EF] to-[#EFEDE8] bg-[length:400%_100%] animate-pulse" />
      </div>
      <div className="space-y-3">
        {[1, 2, 3, 4].map((i) => (
          <div
            key={i}
            className="h-20 rounded-2xl bg-gradient-to-r from-[#EFEDE8] via-[#F5F3EF] to-[#EFEDE8] bg-[length:400%_100%] animate-pulse"
          />
        ))}
      </div>
    </div>
  );
}