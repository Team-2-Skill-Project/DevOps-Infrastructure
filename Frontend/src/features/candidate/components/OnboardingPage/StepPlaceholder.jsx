import { ArrowLeft, ArrowRight } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function StepPlaceholder({ label, field, onContinue, onPrevious }) {
  return (
    <div>
      <h2 className="mb-1.5 text-[20px] font-bold text-ink">{label}</h2>
      <p className="mb-8 text-[13px] text-muted">
        This step&apos;s content hasn&apos;t been sent over yet — send the markup and it&apos;ll
        get wired up the same way as the job title step.
      </p>
      <div className="flex items-center gap-3">
        <Button
          type="button"
          variant="outline"
          onClick={onPrevious}
          className="h-11 flex-1 gap-1.5 rounded-xl border-border text-[13px] font-bold text-muted"
        >
          <ArrowLeft className="h-4 w-4" />
          Previous
        </Button>
        <Button
          type="button"
          onClick={() => onContinue(field, "")}
          className="h-11 flex-1 gap-1.5 rounded-xl bg-primary text-[13px] font-bold text-primary-foreground hover:bg-primary/90"
        >
          Continue
          <ArrowRight className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}
