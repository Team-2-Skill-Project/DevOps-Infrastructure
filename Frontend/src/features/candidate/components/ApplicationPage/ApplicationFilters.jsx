import { Search, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { STATUS_CONFIG } from "@/constants/application";

export default function ApplicationFilters({
  searchQuery,
  onSearchChange,
  sortBy,
  onSortChange,
  statusFilter,
  onStatusChange,
}) {
  return (
    <>
      <div className="flex flex-col gap-2.5 sm:flex-row">
        <div className="flex min-w-0 flex-1 items-center gap-2 border border-border rounded-full bg-white h-11 px-4 shadow-sm focus-within:border-primary transition-colors">
          <Search className="w-4 h-4 text-muted shrink-0" />
          <Input
            type="text"
            value={searchQuery}
            onChange={(event) => onSearchChange(event.target.value)}
            placeholder="Search by job title or company…"
            className="flex-1 h-auto border-0 p-0 outline-none text-[13.5px] placeholder:text-muted bg-transparent shadow-none focus-visible:ring-0"
          />
          {searchQuery && (
            <Button
              type="button"
              onClick={() => onSearchChange("")}
              variant="ghost"
              size="icon-xs"
              className="text-muted hover:text-[#1B1C1A] shrink-0"
            >
              <X className="w-4 h-4" />
            </Button>
          )}
        </div>

        <Select value={sortBy} onValueChange={onSortChange}>
          <SelectTrigger className="h-11 ps-4 pe-10 border-border rounded-full bg-white text-[13px] font-medium shadow-sm cursor-pointer w-full sm:w-auto">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="recent">Most recent update</SelectItem>
            <SelectItem value="date">Application date</SelectItem>
            <SelectItem value="match">Match level (high–low)</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div className="flex items-center gap-2 overflow-x-auto pb-1 -mx-1 px-1 no-scrollbar">
        {Object.keys(STATUS_CONFIG).map((statusKey) => (
          <Button
            key={statusKey}
            onClick={() => onStatusChange(statusKey)}
            type="button"
            className={`shrink-0 px-3.5 h-8 rounded-full border text-[12px] font-semibold transition-all hover:text-white ${
              statusFilter === statusKey
                ? "bg-primary text-white border-primary shadow-sm"
                : "bg-white text-[#44474E] border-border hover:border-primary/40"
            }`}
          >
            {STATUS_CONFIG[statusKey].label}
          </Button>
        ))}
      </div>
    </>
  );
}
