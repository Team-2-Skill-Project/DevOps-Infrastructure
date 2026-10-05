import { useState } from "react";
import { useTranslation } from "react-i18next";
import { Download, RefreshCw } from "lucide-react";
import { PageHeader, FilterSelect } from "@/features/admin/shared";
import {
  AuditLogTable,
  AuditLogDrawer,
  AuditLogsToolbar,
  AuditLogsListSkeleton,
  AUDIT_LOGS,
  SORT_OPTIONS,
  INITIAL_ACTIVE_FILTERS,
  LOGS_RESULT_SUMMARY,
  LOGS_PAGINATION,
} from "@/features/admin/components/auditLogsList";

/**
 * AuditLogsList (page)
 * Container for the Audit Logs list. Owns page state (loading, active
 * filter chips, selected row) and composes the toolbar, results bar,
 * table and animated preview drawer.
 */
export default function AuditLogsList() {
  const { t } = useTranslation("dashboard");
  const [isLoading, setIsLoading] = useState(false);
  const [selectedLog, setSelectedLog] = useState(null);
  const [filters, setFilters] = useState(INITIAL_ACTIVE_FILTERS);

  const removeFilter = (id) =>
    setFilters((prev) => prev.filter((filter) => filter.id !== id));
  const clearAllFilters = () => setFilters([]);

  const handleRefresh = () => {
    setIsLoading(true);
    setTimeout(() => setIsLoading(false), 1200);
  };

  if (isLoading) return <AuditLogsListSkeleton />;

  return (
    <div className="max-w-7xl mx-auto p-4 sm:p-7 pb-16 bg-background text-[#222831] min-h-screen font-sans antialiased">
      {/* Page Header */}
      <PageHeader
        title={t("pages.admin.auditLogs")}
        subtitle="Complete record of administrative actions across the platform"
        meta={
          <>
            <span>Read-only</span>
            <span className="w-1 h-1 rounded-full bg-border" />
            <span>Last synced · just now</span>
          </>
        }
        actions={
          <>
            <button
              type="button"
              className="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold border border-border bg-white hover:bg-[#f5f2ec] transition-colors"
            >
              <Download className="w-4 h-4" /> Export
            </button>
            <button
              type="button"
              onClick={handleRefresh}
              className="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold bg-primary text-white hover:bg-[#182a4a] transition-colors"
            >
              <RefreshCw className="w-4 h-4" /> Refresh
            </button>
          </>
        }
      />

      {/* Toolbar: Search + Filters + Active chips */}
      <AuditLogsToolbar
        filters={filters}
        onRemoveFilter={removeFilter}
        onClearFilters={clearAllFilters}
      />

      {/* Results Bar */}
      <div className="flex items-center justify-between gap-3 mb-3 flex-wrap">
        <div className="text-xs sm:text-sm text-muted">
          Showing{" "}
          <strong className="text-[#222831] font-semibold">
            {LOGS_RESULT_SUMMARY.from}–{LOGS_RESULT_SUMMARY.to}
          </strong>{" "}
          of{" "}
          <strong className="text-[#222831] font-semibold">
            {LOGS_RESULT_SUMMARY.total}
          </strong>{" "}
          logs
        </div>
        <div className="flex items-center gap-2 text-xs sm:text-sm text-muted">
          <span>Sort by</span>
          <FilterSelect
            size="sm"
            options={SORT_OPTIONS}
            ariaLabel="Sort audit logs"
          />
        </div>
      </div>

      {/* Table */}
      <AuditLogTable
        logs={AUDIT_LOGS}
        onSelectLog={setSelectedLog}
        page={LOGS_PAGINATION.page}
        pageCount={LOGS_PAGINATION.pageCount}
      />

      {/* Log Detail Preview Drawer */}
      <AuditLogDrawer log={selectedLog} onClose={() => setSelectedLog(null)} />
    </div>
  );
}
