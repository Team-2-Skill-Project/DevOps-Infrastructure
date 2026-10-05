import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { motion, AnimatePresence } from "framer-motion";
import { FilterX } from "lucide-react";
import { containerVariants } from "@/features/admin/shared";
import {
  JobManagementToolbar,
  JobRow,
  JobManagementSkeleton,
  JobManagementEmpty,
  JobManagementError,
  INITIAL_JOBS,
  JOB_COLUMNS,
} from "@/features/admin/components/jobManagement";

/**
 * JobManagement (page)
 * Container/page component. Owns data + page state (loading, error, search,
 * filters, sort, active row menu). The rendered view is derived from the real
 * data lifecycle instead of a manual preview switcher.
 */
export default function JobManagement() {
  const { t } = useTranslation("dashboard");
  const [jobs, setJobs] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);
  const [reloadToken, setReloadToken] = useState(0);

  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [sourceFilter, setSourceFilter] = useState("all");
  const [sortBy, setSortBy] = useState("created-desc");
  const [activeMenuId, setActiveMenuId] = useState(null);

  // Load jobs on mount and whenever a retry is requested.
  // TODO: replace the simulated fetch with the real jobs service/API call.
  useEffect(() => {
    let cancelled = false;

    const fetchJobs = async () => {
      try {
        await new Promise((resolve) => setTimeout(resolve, 600));
        if (!cancelled) {
          setJobs(INITIAL_JOBS);
          setError(null);
        }
      } catch (err) {
        if (!cancelled) {
          setError(err);
        }
      } finally {
        if (!cancelled) {
          setIsLoading(false);
        }
      }
    };

    fetchJobs();

    return () => {
      cancelled = true;
    };
  }, [reloadToken]);

  const handleRetry = () => {
    setIsLoading(true);
    setError(null);
    setReloadToken((token) => token + 1);
  };

  // Filter and sort logic
  const filteredJobs = useMemo(() => {
    return jobs
      .filter((job) => {
        const q = searchQuery.toLowerCase();
        const matchesSearch =
          job.title.toLowerCase().includes(q) ||
          job.company.toLowerCase().includes(q);
        const matchesStatus =
          statusFilter === "all" || job.status === statusFilter;
        const matchesSource =
          sourceFilter === "all" || job.source === sourceFilter;

        return matchesSearch && matchesStatus && matchesSource;
      })
      .sort((a, b) => {
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
  }, [jobs, searchQuery, statusFilter, sourceFilter, sortBy]);

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

  // Derive the view from the actual data/loading/error state.
  const hasJobs = jobs.length > 0;

  return (
    <div className="min-h-screen bg-gradient-to-b from-background via-[#F5F2EC] to-background text-[#1B1C1A] font-sans antialiased pb-12">
      {/* ========== MAIN CONTAINER ========== */}
      <main className="max-w-[1180px] mx-auto px-5 py-8">
        {/* Page Title */}
        <div className="mb-6">
          <h1 className="font-extrabold text-[24px] tracking-tight text-[#1B1C1A]">
            {t("pages.admin.jobManagement.title")}
          </h1>
          <p className="text-[13.5px] text-muted mt-1">
            {t("pages.admin.jobManagement.subtitle")}
          </p>
        </div>

        {/* ========== VIEW: LOADING (SKELETON) ========== */}
        {isLoading && <JobManagementSkeleton />}

        {/* ========== VIEW: ERROR ========== */}
        {!isLoading && error && <JobManagementError onRetry={handleRetry} />}

        {/* ========== VIEW: EMPTY (NO JOBS AT ALL) ========== */}
        {!isLoading && !error && !hasJobs && <JobManagementEmpty />}

        {/* ========== VIEW: LOADED (MAIN DASHBOARD) ========== */}
        {!isLoading && !error && hasJobs && (
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
