import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { SORT_OPTIONS } from "@/features/admin/shared/filterOptions";

export default function CompaniesResultsBar({
  total,
  start,
  end,
  sort,
  onSortChange,
}) {
  return (
    <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
      <div className="text-[13.5px] text-muted">
        Showing{" "}
        <strong className="font-semibold text-ink">
          {start}–{end}
        </strong>{" "}
        of <strong className="font-semibold text-ink">{total}</strong>{" "}
        {total === 1 ? "company" : "companies"}
      </div>
      <div className="flex items-center gap-2 text-[13px] text-muted">
        <span>Sort by</span>
        <Select value={sort} onValueChange={onSortChange}>
          <SelectTrigger className="h-[34px] min-w-[160px] rounded-xl border-border bg-surface text-[13px] font-medium text-ink">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            {SORT_OPTIONS.map((option) => (
              <SelectItem key={option.value} value={option.value}>
                {option.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>
    </div>
  );
}
