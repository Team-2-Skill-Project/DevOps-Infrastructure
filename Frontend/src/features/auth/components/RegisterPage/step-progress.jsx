import { cn } from "@/lib/utils";

function StepBar({ label, index, state }) {
  const isActive = state === "active";
  const isComplete = state === "complete";

  return (
    <div
      className="flex min-w-0 flex-1 flex-col gap-2"
      aria-current={isActive ? "step" : undefined}
      role="listitem"
    >
      <div
        className={cn(
          "h-1.5 w-full rounded-full transition-colors duration-300",
          isComplete && "bg-success",
          isActive && "bg-primary",
          state === "upcoming" && "bg-border",
        )}
      />

      <div
        className={cn(
          "break-words text-[11px] font-semibold leading-4 transition-colors sm:text-xs",
          (isActive || isComplete) && "text-ink",
          state === "upcoming" && "text-muted",
        )}
      >
        <span className="mb-1 block text-[10px] font-medium text-muted">
          {String(index + 1).padStart(2, "0")}
        </span>
        {label}
      </div>
    </div>
  );
}

export function StepProgress({ steps, currentStep = 0 }) {
  const progressSteps = Array.isArray(steps) ? steps : [];
  const activeStep = Math.min(
    Math.max(currentStep, 0),
    progressSteps.length - 1,
  );

  return (
    <div className="w-full rounded-xl bg-background px-1 py-2">
      <div
        className="grid w-full items-start gap-3"
        style={{ gridTemplateColumns: `repeat(${progressSteps.length}, minmax(0, 1fr))` }}
        role="list"
      >
        {progressSteps.map((label, index) => {
          const state =
            index < activeStep
              ? "complete"
              : index === activeStep
                ? "active"
                : "upcoming";

          return (
            <StepBar
              key={`${index}-${label}`}
              label={label}
              index={index}
              state={state}
            />
          );
        })}
      </div>
    </div>
  );
}
