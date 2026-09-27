import { Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export default function SavedJobsToolbar({ jobsCount }) {
  return (
    <div className="flex flex-wrap items-center justify-between gap-3 sm:gap-4 mb-6 bg-surface border border-border p-3 sm:p-4 rounded-xl shadow-xs">
      <div className="flex w-full sm:w-auto items-center gap-2 overflow-x-auto pb-1 md:pb-0">
        <Button className="bg-secondary text-secondary-foreground text-xs font-semibold px-3.5 py-2 rounded-lg">
          <span>All Saved</span>
          <span className="ms-1.5 bg-surface/20 text-[10px] px-1.5 py-0.5 rounded-full">
            {jobsCount}
          </span>
        </Button>
        {["High Fit (≥75%)", "Fresh Added", "Applied"].map((filter) => (
          <Button
            key={filter}
            className="bg-background text-primary border border-border text-xs font-semibold px-3.5 py-2 rounded-lg"
          >
            {filter}
          </Button>
        ))}
      </div>
      <Button className="w-full lg:w-auto text-xs font-semibold text-primary border border-border bg-background px-3.5 py-2 rounded-lg">
        Sort: Best Match
      </Button>
    </div>
  );
}
