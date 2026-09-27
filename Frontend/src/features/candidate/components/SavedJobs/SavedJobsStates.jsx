import { Briefcase } from "lucide-react";
import Status from "@/components/shared/Status";

export function EmptySavedJobs() {
  return (
    <div className="bg-surface border border-border rounded-2xl shadow-xs">
      <Status
        icon={<Briefcase className="h-7 w-7 text-muted" />}
        iconClassName="border border-border bg-background"
        title="No Saved Roles Found"
        subtitle="Your saved opportunities will appear here when you bookmark a role."
      />
    </div>
  );
}

export function PortfolioHealth() {
  return (
    <div className="bg-surface border border-border rounded-2xl p-6 shadow-xs">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <h4 className="font-dm-sans text-lg font-bold text-primary">
          Portfolio Health
        </h4>
        <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-background border border-border text-primary">
          Strong Affinity
        </span>
      </div>
      <div className="mb-4">
        <div className="flex justify-between text-xs mb-1.5">
          <span className="text-muted">Overall Match Fit</span>
          <span className="font-bold text-primary">79% Average</span>
        </div>
        <div className="w-full bg-border h-2.5 rounded-full overflow-hidden">
          <div className="bg-primary h-full" style={{ width: "79%" }} />
        </div>
      </div>
      <div className="grid grid-cols-3 gap-2 text-center pt-3 border-t border-border text-xs">
        {[
          ["5", "High (≥75%)"],
          ["4", "Solid (60-74%)"],
          ["3", "Gaps (<60%)"],
        ].map(([value, label]) => (
          <div
            key={label}
            className="p-2 rounded-lg bg-background border border-border"
          >
            <span className="block font-bold text-primary text-sm">
              {value}
            </span>
            <span className="text-[10px] text-muted">{label}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
