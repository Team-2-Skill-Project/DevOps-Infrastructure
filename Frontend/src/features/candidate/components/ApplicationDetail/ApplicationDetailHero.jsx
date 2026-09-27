import {
  CalendarClock,
  ExternalLink,
  FileText,
  Link2,
  Map,
  MapPin,
  MessageSquareText,
  Mic,
  PenLine,
  XCircle,
} from "lucide-react";
import MatchRing from "../ApplicationPage/MatchRing";

export default function ApplicationDetailHero({
  isBannerState,
  setShowNoteForm,
  handleWithdraw,
}) {
  return (
    <div className="bg-white border border-border rounded-3xl p-6 mb-5 shadow-sm">
      <div className="flex flex-col sm:flex-row sm:items-start gap-4">
        <div className="bg-linear-to-br from-primary to-[#3B557D] w-16 h-16 rounded-2xl flex items-center justify-center shrink-0 shadow-sm">
          <span className="font-display font-extrabold text-[22px] text-white">
            V
          </span>
        </div>
        <div className="flex-1 min-w-0">
          <h1 className="font-display font-extrabold text-[21px] leading-tight mb-1">
            Senior Frontend Engineer
          </h1>
          <div className="text-[13.5px] text-[#44474E]">Vercel · Remote</div>
          <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-muted mt-1.5">
            <span>Applied Aug 24, 2026</span>
            <span className="flex items-center gap-1">
              <CalendarClock className="h-3.5 w-3.5" />
              Updated Aug 27, 2026
            </span>
            <span className="flex items-center gap-1">
              <MapPin className="h-3.5 w-3.5" />
              Source: SkillMatch Feed
            </span>
          </div>
        </div>
        <div className="flex flex-col items-end gap-2 shrink-0">
          <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold bg-primary/10 text-primary">
            <span className="w-1.5 h-1.5 rounded-full bg-primary" />
            Under Review
          </span>
          <div className="flex items-center gap-2">
            <MatchRing score={98} />
            <span className="text-[11px] text-muted">match</span>
          </div>
        </div>
      </div>

      <div className="flex flex-wrap gap-5 mt-5 pt-5 border-t border-border">
        <a
          href="#view-job"
          className="h-9 px-4 rounded-full border border-border text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-background transition-colors"
        >
          <ExternalLink className="h-4 w-4" />
          View Job
        </a>
        <a
          href="#view-cv"
          className="h-9 px-4 rounded-full border border-border text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-background transition-colors"
        >
          <FileText className="h-4 w-4" />
          View CV
        </a>
        <a
          href="#open-source"
          className="h-9 px-4 rounded-full border border-border text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-background transition-colors"
        >
          <Link2 className="h-4 w-4" />
          Open Source
        </a>
        <a
          href="#ask-mentor"
          className="h-9 px-4 rounded-full bg-primary text-white text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-[#0F2036] transition-colors"
        >
          <MessageSquareText className="h-4 w-4" />
          Ask Mentor
        </a>
        <button
          type="button"
          onClick={() => setShowNoteForm((prev) => !prev)}
          className="h-9 px-4 rounded-full border border-border text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-background transition-colors"
        >
          <PenLine className="h-4 w-4" />
          Add Note
        </button>

        {/* {!isBannerState && (
          <a
            href="#prep"
            className="h-9 px-4 rounded-full border border-warning/40 text-warning text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-warning/5 transition-colors"
          >
            <Mic className="h-4 w-4" />
            Prepare for Interview
            <span className="text-[9px] font-bold bg-warning/15 rounded-full px-1.5 py-0.5">
              PHASE 2
            </span>
          </a>
        )} */}

        <a
          href="#roadmap"
          className="h-9 px-4 rounded-full border border-border text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-background transition-colors"
        >
          <Map className="h-4 w-4" />
          Add to Roadmap
        </a>

        {!isBannerState && (
          <button
            type="button"
            onClick={handleWithdraw}
            className="h-9 px-4 rounded-full border border-[#B3271E]/30 text-[#B3271E] text-[12.5px] font-semibold flex items-center gap-1.5 hover:bg-[#B3271E]/5 transition-colors sm:ms-auto"
          >
            <XCircle className="h-4 w-4" />
            Withdraw
          </button>
        )}
      </div>
    </div>
  );
}
