import { Check, ClipboardList, Trophy, Users, X } from "lucide-react";

export default function ApplicationDetailTimeline({
  viewState,
  isBannerState,
}) {
  return (
    <div className="bg-white border border-border rounded-2xl p-6 mb-5 shadow-sm">
      <h2 className="font-display font-bold text-[15px] mb-5">
        Application timeline
      </h2>

      {!isBannerState ? (
        <div className="relative ps-1">
          <div className="absolute inset-s-3.75 top-2 bottom-2 w-px bg-border" />

          <div className="relative flex gap-4 pb-6">
            <div className="w-8 h-8 rounded-full bg-success text-white flex items-center justify-center shrink-0 z-10">
              <Check className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold">Applied</div>
              <div className="text-[12px] text-muted mt-0.5">
                Aug 24, 2026 · 10:12 AM
              </div>
            </div>
          </div>

          <div className="relative flex gap-4 pb-6">
            <div className="w-8 h-8 rounded-full bg-success text-white flex items-center justify-center shrink-0 z-10">
              <Check className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold">Under Review</div>
              <div className="text-[12px] text-muted mt-0.5">
                Aug 27, 2026 · 3:40 PM
              </div>
            </div>
          </div>

          <div className="relative flex gap-4 pb-6">
            <div className="w-8 h-8 rounded-full bg-white border-2 border-border text-muted flex items-center justify-center shrink-0 z-10">
              <Users className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold text-muted">
                Interview
              </div>
              <div className="text-[12px] text-muted mt-0.5">
                Not yet scheduled
              </div>
            </div>
          </div>

          <div className="relative flex gap-4 pb-6">
            <div className="w-8 h-8 rounded-full bg-white border-2 border-border text-muted flex items-center justify-center shrink-0 z-10">
              <ClipboardList className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold text-muted">
                Assessment
              </div>
              <div className="text-[12px] text-muted mt-0.5">
                Pending previous steps
              </div>
            </div>
          </div>

          <div className="relative flex gap-4">
            <div className="w-8 h-8 rounded-full bg-white border-2 border-border text-muted flex items-center justify-center shrink-0 z-10">
              <Trophy className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold text-muted">Offer</div>
              <div className="text-[12px] text-muted mt-0.5">
                Pending previous steps
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div className="relative ps-1">
          <div className="relative flex gap-4 pb-6">
            <div className="w-8 h-8 rounded-full bg-success text-white flex items-center justify-center shrink-0 z-10">
              <Check className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold">Applied</div>
              <div className="text-[12px] text-muted mt-0.5">
                Aug 24, 2026 · 10:12 AM
              </div>
            </div>
          </div>
          <div className="relative flex gap-4">
            <div className="w-8 h-8 rounded-full bg-[#B3271E] text-white flex items-center justify-center shrink-0 z-10">
              <X className="h-4 w-4" />
            </div>
            <div className="pt-1">
              <div className="text-[13px] font-semibold text-[#B3271E] capitalize">
                {viewState === "withdrawn"
                  ? "Withdrawn"
                  : viewState === "closed"
                    ? "Closed by employer"
                    : "Expired"}
              </div>
              <div className="text-[12px] text-muted mt-0.5">
                {viewState === "withdrawn"
                  ? "Sep 10, 2026 · 9:05 AM"
                  : viewState === "closed"
                    ? "Sep 14, 2026"
                    : "Sep 20, 2026"}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
