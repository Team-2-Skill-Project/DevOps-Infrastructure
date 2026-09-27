import { AnimatePresence, motion } from "framer-motion";
import { Briefcase, ExternalLink, MoreVertical, XCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import MatchRing from "./MatchRing";
import { STATUS_CONFIG } from "@/constants/application";

export default function ApplicationCard({
  app,
  openMenuId,
  onMenuToggle,
  onOpenDetails,
  onWithdraw,
}) {
  const statusObj = STATUS_CONFIG[app.status] || STATUS_CONFIG.applied;
  const StatusIcon = statusObj.icon;

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, scale: 0.96 }}
      transition={{ duration: 0.25 }}
      className={`bg-white border rounded-2xl p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row sm:items-center gap-4 ${app.status === "offer" ? "border-success/30 bg-emerald-50/10" : "border-border"} ${app.status === "rejected" || app.status === "withdrawn" ? "opacity-85" : ""}`}
    >
      <div className="flex items-center gap-3.5 flex-1 min-w-0">
        <div
          className="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-[14px] text-white shrink-0 shadow-inner"
          style={{ background: app.bgGradient }}
        >
          <span
            className={app.status === "withdrawn" ? "text-muted" : "text-white"}
          >
            {app.avatarLetter}
          </span>
        </div>
        <div className="min-w-0">
          <div className="text-[14px] font-semibold truncate text-[#1B1C1A]">
            {app.title}
          </div>
          <div className="text-[11.5px] text-muted">
            {app.company} · via {app.source}
          </div>
        </div>
      </div>

      <div className="flex w-full min-w-0 flex-wrap items-center justify-between sm:w-auto sm:justify-end gap-2 sm:gap-6 shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-border/50">
        <span
          className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11.5px] font-semibold ${statusObj.color}`}
        >
          {StatusIcon ? (
            <StatusIcon className="w-3.5 h-3.5" />
          ) : (
            <span className={`w-1.5 h-1.5 rounded-full ${statusObj.dot}`} />
          )}
          {statusObj.label}
        </span>
        <MatchRing score={app.matchScore} />
        <div className="hidden md:block text-[12px] text-muted w-20 text-right">
          {new Date(app.appliedDate).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
          })}
        </div>
        <div className="flex items-center gap-1">
          <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            onClick={() => onOpenDetails(app)}
            className="w-8 h-8 rounded-full flex items-center justify-center text-primary hover:bg-primary/10 transition-colors"
            title="Open Application Details"
          >
            <ExternalLink className="w-4 h-4" />
          </Button>
          <div className="relative">
            <Button
              type="button"
              variant="ghost"
              size="icon-sm"
              onClick={(event) => onMenuToggle(event, app.id)}
              className="w-8 h-8 rounded-full flex items-center justify-center text-muted hover:bg-background transition-colors"
            >
              <MoreVertical className="w-4 h-4" />
            </Button>
            <AnimatePresence>
              {openMenuId === app.id && (
                <motion.div
                  initial={{ opacity: 0, scale: 0.95, y: -5 }}
                  animate={{ opacity: 1, scale: 1, y: 0 }}
                  exit={{ opacity: 0, scale: 0.95, y: -5 }}
                  transition={{ duration: 0.15 }}
                  className="absolute inset-e-0 top-9 z-20 bg-white border border-border rounded-xl shadow-lg py-1.5 w-44"
                  onClick={(event) => event.stopPropagation()}
                >
                  <Button
                    type="button"
                    variant="ghost"
                    onClick={() => onOpenDetails(app)}
                    className="w-full text-left flex items-center gap-2 px-3 py-2 text-[12.5px] text-[#44474E] hover:bg-background transition-colors"
                  >
                    <Briefcase className="w-4 h-4 text-muted" /> View Details
                  </Button>
                  {app.status !== "withdrawn" && app.status !== "rejected" && (
                    <Button
                      type="button"
                      variant="ghost"
                      onClick={() => onWithdraw(app.id)}
                      className="w-full text-left flex items-center gap-2 px-3 py-2 text-[12.5px] text-[#B3271E] hover:bg-[#B3271E]/5 transition-colors border-t border-border/50 mt-1 pt-1.5"
                    >
                      <XCircle className="w-4 h-4" /> Withdraw Application
                    </Button>
                  )}
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        </div>
      </div>
    </motion.div>
  );
}
