import { Search, X } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  STATUS_FILTER_OPTIONS,
  DATE_FILTER_OPTIONS,
} from "@/features/admin/shared/filterOptions";

export default function CompaniesToolbar({
  search,
  onSearchChange,
  statusFilter,
  onStatusFilterChange,
  dateFilter,
  onDateFilterChange,
  onClearFilters,
}) {
  const activeChips = [];
  if (statusFilter !== "all") {
    const option = STATUS_FILTER_OPTIONS.find((o) => o.value === statusFilter);
    activeChips.push({
      key: "status",
      label: `Status: ${option.label}`,
      onRemove: () => onStatusFilterChange("all"),
    });
  }
  if (dateFilter !== "any") {
    const option = DATE_FILTER_OPTIONS.find((o) => o.value === dateFilter);
    activeChips.push({
      key: "date",
      label: option.label,
      onRemove: () => onDateFilterChange("any"),
    });
  }

  return (
    <div className="mb-4 flex flex-col gap-3">
      <div className="flex flex-wrap items-center gap-3">
        <div className="relative min-w-[220px] flex-1">
          <Search className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-muted" />
          <Input
            type="search"
            value={search}
            onChange={(e) => onSearchChange(e.target.value)}
            placeholder="Search by company name, email, or ID…"
            className="h-[42px] rounded-xl border-border bg-surface pl-10 text-[14px] text-ink shadow-none placeholder:text-muted focus-visible:border-primary focus-visible:ring-0"
          />
        </div>

        <Select value={statusFilter} onValueChange={onStatusFilterChange}>
          <SelectTrigger className="h-[42px] min-w-[150px] rounded-xl border-border bg-surface text-[13.5px] font-medium text-ink">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            {STATUS_FILTER_OPTIONS.map((option) => (
              <SelectItem key={option.value} value={option.value}>
                {option.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>

        <Select value={dateFilter} onValueChange={onDateFilterChange}>
          <SelectTrigger className="h-[42px] min-w-[150px] rounded-xl border-border bg-surface text-[13.5px] font-medium text-ink">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            {DATE_FILTER_OPTIONS.map((option) => (
              <SelectItem key={option.value} value={option.value}>
                {option.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {activeChips.length > 0 && (
        <div className="flex flex-wrap items-center gap-2">
          {activeChips.map((chip) => (
            <span
              key={chip.key}
              className="inline-flex h-7 items-center gap-1.5 rounded-full bg-primary/10 px-2.5 text-[12.5px] font-semibold text-primary"
            >
              {chip.label}
              <button
                type="button"
                onClick={chip.onRemove}
                aria-label="Remove filter"
                className="opacity-65 hover:opacity-100"
              >
                <X className="h-3 w-3" />
              </button>
            </span>
          ))}
          <Button
            type="button"
            variant="ghost"
            onClick={onClearFilters}
            className="h-auto px-1 text-[12.5px] font-semibold text-muted hover:bg-transparent hover:text-ink hover:underline"
          >
            Clear all
          </Button>
        </div>
      )}
    </div>
  );
}
