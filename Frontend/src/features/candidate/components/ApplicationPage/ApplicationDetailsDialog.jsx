import { Briefcase } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogTitle } from "@/components/ui/dialog";

export default function ApplicationDetailsDialog({ app, onClose, onToast }) {
  return (
    <Dialog open={Boolean(app)} onOpenChange={(open) => !open && onClose()}>
      <DialogContent className="bg-white border-border rounded-3xl w-[calc(100%-2rem)] max-h-[calc(100vh-2rem)] overflow-y-auto max-w-lg p-4 sm:p-6 shadow-xl">
        {app && (
          <>
            <div className="flex items-center gap-3.5 mb-5">
              <div
                className="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-[16px] text-white shrink-0"
                style={{ background: app.bgGradient }}
              >
                {app.avatarLetter}
              </div>
              <div>
                <DialogTitle className="font-dm-sans font-bold text-[18px] leading-tight text-[#1B1C1A]">
                  {app.title}
                </DialogTitle>
                <p className="text-[13px] text-muted">{app.company}</p>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3 mb-6 bg-background p-3.5 rounded-2xl border border-border">
              <div>
                <span className="text-[11px] font-medium text-muted block">
                  Match Score
                </span>
                <span className="text-[15px] font-bold text-success">
                  {app.matchScore}% Match
                </span>
              </div>
              <div>
                <span className="text-[11px] font-medium text-muted block">
                  Applied Date
                </span>
                <span className="text-[13.5px] font-semibold text-[#1B1C1A]">
                  {app.appliedDate}
                </span>
              </div>
              <div className="col-span-2 pt-2 border-t border-border">
                <span className="text-[11px] font-medium text-muted block">
                  Current Status
                </span>
                <span className="text-[13px] font-semibold text-primary capitalize">
                  {app.status.replace("-", " ")}
                </span>
              </div>
            </div>
            <div className="flex flex-col sm:flex-row gap-2">
              <Button
                onClick={() => {
                  onToast("Job posting details opened in external view.");
                  onClose();
                }}
                className="w-full sm:flex-1 bg-primary text-white text-[13px] font-semibold h-10 rounded-full hover:bg-[#0F2036] transition-colors"
              >
                <Briefcase className="w-4 h-4" /> View Full Job
              </Button>
              <Button
                variant="outline"
                onClick={onClose}
                className="w-full sm:w-auto px-5 border-border text-[13px] font-semibold h-10 rounded-full hover:bg-background transition-colors text-[#44474E]"
              >
                Close
              </Button>
            </div>
          </>
        )}
      </DialogContent>
    </Dialog>
  );
}
