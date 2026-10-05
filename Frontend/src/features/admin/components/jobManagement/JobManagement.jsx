import { useMemo, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { FilterX } from "lucide-react";
import { containerVariants } from "@/features/admin/shared";

import JobManagementToolbar from "./JobManagementToolbar";
import JobRow from "./JobRow";
import JobManagementSkeleton from "./JobManagementSkeleton";
import JobManagementEmpty from "./JobManagementEmpty";
import JobManagementError from "./JobManagementError";
import { INITIAL_JOBS, JOB_COLUMNS } from "../../shared/jobManagementData";

/**
 * JobManagement
 * Main container/page component. Owns page state (view mode, search, filters,
 * sort, active row menu) and composes all single-responsibility sub-components.
 */
export default function JobManagement() {
  const [viewState, setViewState] = useState("loaded"); // 'loading' | 'loaded' | 'empty' | 'error'
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [sourceFilter, setSourceFilter] = useState("all");
  const [sortBy, setSortBy] = useState("created-desc");
  const [activeMenuId, setActiveMenuId] = useState(null);

  // Filter and sort logic
  const filteredJobs = useMemo(() => {
    return INITIAL_JOBS.filter((job) => {
      const q = searchQuery.toLowerCase();
      const matchesSearch =
        job.title.toLowerCase().includes(q) ||
        job.company.toLowerCase().includes(q);
      const matchesStatus =
        statusFilter === "all" || job.status === statusFilter;
      const matchesSource =
        sourceFilter === "all" || job.source === sourceFilter;

      return matchesSearch && matchesStatus && matchesSource;
    }).sort((a, b) => {
      if (sortBy === "published-desc") {
        return (
          new Date(b.rawPubDate || 0).getTime() -
          new Date(a.rawPubDate || 0).getTime()
        );
      }
      if (sortBy === "freshness") {
        return a.freshnessDays - b.freshnessDays;
      }
      return (
        new Date(b.rawCreatedDate).getTime() -
        new Date(a.rawCreatedDate).getTime()
      );
    });
  }, [searchQuery, statusFilter, sourceFilter, sortBy]);

  const clearFilters = () => {
    setSearchQuery("");
    setStatusFilter("all");
    setSourceFilter("all");
  };

  const handleAction = (msg) => {
    // TODO: wire to real row actions (view/edit/company/update)
    alert(msg);
    setActiveMenuId(null);
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-background via-[#F5F2EC] to-background text-[#1B1C1A] font-sans antialiased pb-12">
      {/* ========== PREVIEW STATE SWITCHER (DEMO ONLY) ========== */}
      <div className="bg-[#0F2036] text-white text-[12px] flex items-center justify-center gap-2 py-2 px-4 flex-wrap sticky top-0 z-50 border-b border-white/10">
        <span className="text-white/50 mr-1 font-medium">Preview state:</span>
        {["loading", "loaded", "empty", "error"].map((state) => (
          <button
            key={state}
            type="button"
            onClick={() => setViewState(state)}
            className={`px-3 py-0.5 rounded-md border text-[11.5px] font-medium transition-colors ${
              viewState === state
                ? "bg-white/20 border-white text-white"
                : "border-white/20 text-white/80 hover:bg-white/10"
            }`}
          >
            {state.charAt(0).toUpperCase() + state.slice(1)}
          </button>
        ))}
      </div>

      {/* ========== MAIN CONTAINER ========== */}
      <main className="max-w-[1180px] mx-auto px-5 py-8">
        {/* Page Title */}
        <div className="mb-6">
          <h1 className="font-extrabold text-[24px] tracking-tight text-[#1B1C1A]">
            Job Management
          </h1>
          <p className="text-[13.5px] text-muted mt-1">
            Monitor and manage every job listed on SkillMatch — internal
            postings and external sources alike.
          </p>
        </div>

        {/* ========== VIEW: LOADING (SKELETON) ========== */}
        {viewState === "loading" && <JobManagementSkeleton />}

        {/* ========== VIEW: EMPTY (NO JOBS AT ALL) ========== */}
        {viewState === "empty" && <JobManagementEmpty />}

        {/* ========== VIEW: ERROR ========== */}
        {viewState === "error" && (
          <JobManagementError onRetry={() => setViewState("loaded")} />
        )}

        {/* ========== VIEW: LOADED (MAIN DASHBOARD) ========== */}
        {viewState === "loaded" && (
          <div>
            <JobManagementToolbar
              searchQuery={searchQuery}
              onSearchChange={setSearchQuery}
              statusFilter={statusFilter}
              onStatusChange={setStatusFilter}
              sourceFilter={sourceFilter}
              onSourceChange={setSourceFilter}
              sortBy={sortBy}
              onSortChange={setSortBy}
              onClearFilters={clearFilters}
            />

            {/* Column Headers (Desktop) */}
            <div className="hidden lg:grid grid-cols-12 gap-3 px-4 py-2 mt-4 text-[10.5px] font-semibold text-muted uppercase tracking-wide">
              {JOB_COLUMNS.map((column) => (
                <div key={column.label} className={column.className}>
                  {column.label}
                </div>
              ))}
            </div>

            {/* Job Rows Container */}
            <motion.div
              variants={containerVariants}
              initial="hidden"
              animate="visible"
              className="space-y-3"
            >
              <AnimatePresence mode="popLayout">
                {filteredJobs.map((job) => (
                  <JobRow
                    key={job.id}
                    job={job}
                    isMenuOpen={activeMenuId === job.id}
                    onToggleMenu={(open) =>
                      setActiveMenuId(open ? job.id : null)
                    }
                    onAction={handleAction}
                  />
                ))}
              </AnimatePresence>
            </motion.div>

            {/* No Results Fallback */}
            {filteredJobs.length === 0 && (
              <motion.div
                initial={{ opacity: 0, scale: 0.98 }}
                animate={{ opacity: 1, scale: 1 }}
                className="py-16 text-center bg-white border border-border rounded-2xl mt-3 shadow-sm"
              >
                <FilterX className="w-8 h-8 text-muted mx-auto mb-2" />
                <p className="text-[13.5px] text-muted">
                  No jobs match your search or filters.
                </p>
                <button
                  type="button"
                  onClick={clearFilters}
                  className="mt-3 text-[12.5px] font-semibold text-primary hover:underline"
                >
                  Clear filters
                </button>
              </motion.div>
            )}
          </div>
        )}
      </main>
    </div>
  );
}
