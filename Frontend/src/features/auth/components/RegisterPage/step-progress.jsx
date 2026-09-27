import * as React from "react";
import { Progress } from "@/components/ui/progress";
import { cn } from "@/lib/utils";

function StepBar({ label, value, state }) {
  const isActive = state === "active";
  const isComplete = state === "complete";

  return (
    <div className="flex flex-1 flex-col gap-2">
      <Progress
        value={value}
        className="h-1.5 bg-border"
        indicatorClassName={cn(
          "transition-colors duration-500",
          isComplete ? "bg-success" : "bg-primary",
        )}
      />

      <span
        className={cn(
          "text-[12px] font-[600] transition-colors",
          (isActive || isComplete) && "font-medium text-ink",
          !isActive && !isComplete && "text-muted",
        )}
      >
        {label}
      </span>
    </div>
  );
}

 // StepProgress

export function StepProgress({ steps, currentStep = 0 }) {
  return (
    <div className="w-full rounded-xl bg-background p-6">
      <div className="flex w-full items-start gap-4">
        {steps.map((label, index) => {
          const state =
            index < currentStep
              ? "complete"
              : index === currentStep
                ? "active"
                : "upcoming";

          const value =
            index < currentStep ? 100 : index === currentStep ? 100 : 0;

          return (
            <StepBar key={label} label={label} value={value} state={state} />
          );
        })}
      </div>
    </div>
  );
}

export default function Demo() {
  const [step, setStep] = React.useState(0);
  const steps = [
    "Create account",
    "Verify email",
    "Upload CV",
    "Complete profile",
  ];

  return (
    <div className="mx-auto flex max-w-2xl flex-col gap-6 p-8">
      <StepProgress steps={steps} currentStep={step} />

      <div className="flex justify-center gap-3">
        <button
          onClick={() => setStep((s) => Math.max(0, s - 1))}
          className="rounded-lg border border-border px-4 py-2 text-[12px] font-[600] text-ink hover:bg-background"
        >
          Back
        </button>
        <button
          onClick={() => setStep((s) => Math.min(steps.length - 1, s + 1))}
          className="rounded-lg bg-primary px-4 py-2 text-[12px] font-[600] text-white hover:opacity-90"
        >
          Next
        </button>
      </div>
    </div>
  );
}
