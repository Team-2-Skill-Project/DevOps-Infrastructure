import { FilterX } from "lucide-react";
import {
  SearchInput,
  SortDropdown,
  FilterPillGroup,
} from "@/features/admin/shared";
import {
  STATUS_FILTERS,
  SOURCE_FILTERS,
  SORT_OPTIONS,
} from "../../shared/jobManagementData";

/**
 * JobManagementToolbar
 * Search input, status/source filter pills, sort dropdown and "Clear Filters".
 */
export default function JobManagementToolbar({
  searchQuery,
  onSearchChange,
  statusFilter,
  onStatusChange,
  sourceFilter,
  onSourceChange,
  sortBy,
  onSortChange,
  onClearFilters,
}) {
  return (
    <div className="space-y-4">
      {/* Search + Sort Bar */}
      <div className="flex flex-col sm:flex-row gap-2.5">
        <SearchInput
          value={searchQuery}
          onChange={onSearchChange}
          placeholder="Search by job title or company..."
        />
        <SortDropdown
          value={sortBy}
          onChange={onSortChange}
          options={SORT_OPTIONS}
        />
      </div>

      {/* Filter Pills + Clear Filters */}
      <div className="flex items-center gap-2">
        <FilterPillGroup
          options={STATUS_FILTERS}
          value={statusFilter}
          onChange={onStatusChange}
          className="flex-1"
        />

        <span className="w-px h-5 bg-border shrink-0 mx-1" />

        <FilterPillGroup
          options={SOURCE_FILTERS}
          value={sourceFilter}
          onChange={onSourceChange}
          className="flex-1"
        />

        <button
          type="button"
          onClick={onClearFilters}
          className="shrink-0 inline-flex items-center gap-1.5 h-8 px-3 rounded-full border border-border bg-white text-[12px] font-semibold text-[#44474E] hover:bg-background transition-colors"
        >
          <FilterX className="w-3.5 h-3.5 text-muted" />
          Clear Filters
        </button>
      </div>
    </div>
  );
}
