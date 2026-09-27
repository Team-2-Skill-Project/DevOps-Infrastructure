import { ONBOARDING_STEP_FIELDS } from "@/constants/onboardingstepfields";

export default function StepperHeader({ currentStep }) {
  const percent = Math.round(
    (currentStep / ONBOARDING_STEP_FIELDS.length) * 100,
  );

  return (
    <div className="mb-8">
      <div className="mb-3 flex items-center justify-between">
        <span className="inline-flex items-center gap-1.5 text-[12px] font-semibold text-ink">
          <span className="h-1.5 w-1.5 rounded-full bg-error" />
          Smart Account Setup
        </span>
        <span className="text-[12px] text-muted">
          Step {currentStep} of {ONBOARDING_STEP_FIELDS.length}{" "}
          <strong className="font-bold text-primary">{percent}%</strong>
        </span>
      </div>

      <div className="mb-4 h-1.5 w-full overflow-hidden rounded-full bg-background">
        <div
          className="h-full rounded-full bg-primary transition-all duration-500 ease-out"
          style={{ width: `${percent}%` }}
        />
      </div>

      <nav className="flex flex-wrap gap-x-4 gap-y-1.5">
        {ONBOARDING_STEP_FIELDS.map((step) => (
          <span
            key={step.number}
            className={`text-[12px] font-medium ${
              step.number === currentStep
                ? "font-bold text-primary"
                : "text-muted"
            } ${step.number > currentStep ? "opacity-50" : ""}`}
          >
            {step.number}. {step.navLabel}
          </span>
        ))}
      </nav>
    </div>
  );
}
