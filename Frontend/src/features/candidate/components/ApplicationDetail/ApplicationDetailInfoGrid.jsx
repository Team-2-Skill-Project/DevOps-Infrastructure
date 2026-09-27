import { FileText } from "lucide-react";

export default function ApplicationDetailInfoGrid() {
  return (
    <div className="grid sm:grid-cols-2 gap-5 mb-5">
      <div className="bg-white border border-border rounded-2xl p-6 shadow-sm">
        <h2 className="font-display font-bold text-[15px] mb-4">
          Submitted information
        </h2>
        <div className="space-y-2.5 text-[13px]">
          <div className="flex justify-between">
            <span className="text-muted">Name</span>
            <span className="font-medium">Sara Ahmed</span>
          </div>
          <div className="flex justify-between">
            <span className="text-muted">Email</span>
            <span className="font-medium">sara@example.com</span>
          </div>
          <div className="flex justify-between">
            <span className="text-muted">Location</span>
            <span className="font-medium">Damietta, Egypt</span>
          </div>
          <div className="flex justify-between">
            <span className="text-muted">Method</span>
            <span className="font-medium">Internal</span>
          </div>
        </div>
      </div>

      <div className="bg-white border border-border rounded-2xl p-6 shadow-sm">
        <h2 className="font-display font-bold text-[15px] mb-4">
          Submitted CV
        </h2>
        <div className="flex items-center gap-3 border border-border rounded-xl p-3">
          <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
            <FileText className="h-5 w-5" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="text-[13px] font-semibold truncate">
              resume_2026.pdf
            </div>
            <div className="text-[11px] text-muted">Submitted Aug 24, 2026</div>
          </div>
          <a
            href="#view"
            className="text-[12px] font-semibold text-primary shrink-0"
          >
            View
          </a>
        </div>
      </div>
    </div>
  );
}
