import { Search, X } from "lucide-react";
import { FilterSelect } from "@/features/admin/shared";
import {
  ACTION_FILTERS,
  ENTITY_FILTERS,
  ACTOR_FILTERS,
} from "../../shared/auditLogsListData";

/**
 * AuditLogsToolbar
 * Search input, action/entity/actor filter selects and the active
 * filter-chip row with "Clear all".
 */
export default function AuditLogsToolbar({
  filters = [],
  onRemoveFilter,
  onClearFilters,
}) {
  return (
    <div className="space-y-3.5 mb-5">
      <div className="flex flex-wrap gap-3 items-center">
        <div className="flex-1 min-w-60 relative">
          <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" />
          <input
            type="search"
            placeholder="Search by actor, action, entity ID, or IP…"
            className="w-full h-10.5 pl-10 pr-3.5 border border-border rounded-xl text-sm bg-white outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted"
          />
        </div>
        <FilterSelect options={ACTION_FILTERS} ariaLabel="Filter by action" />
        <FilterSelect options={ENTITY_FILTERS} ariaLabel="Filter by entity" />
        <FilterSelect options={ACTOR_FILTERS} ariaLabel="Filter by actor" />
      </div>

      {/* Active filter chips */}
      {filters.length > 0 && (
        <div className="flex flex-wrap gap-2 items-center">
          {filters.map((filter) => (
            <span
              key={filter.id}
              className="inline-flex items-center gap-1.5 h-7 px-2.5 bg-primary/10 text-primary rounded-full text-xs font-semibold"
            >
              {filter.label}
              <button
                type="button"
                aria-label={`Remove filter ${filter.label}`}
                onClick={() => onRemoveFilter?.(filter.id)}
                className="hover:opacity-75"
              >
                <X className="w-3 h-3" />
              </button>
            </span>
          ))}
          <button
            type="button"
            onClick={onClearFilters}
            className="text-xs font-semibold text-muted hover:text-[#222831] hover:underline px-1"
          >
            Clear all
          </button>
        </div>
      )}
    </div>
  );
}
