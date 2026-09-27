import { useState } from "react";
import ActionBanner from "@/components/shared/ActionBanner";

import { ONBOARDING_STEP_FIELDS } from "@/constants/onboardingstepfields";
import StepperHeader from "../components/OnboardingPage/StepperHeader";
import OnboardingCompletedSummary from "../components/OnboardingPage/OnboardingCompletedSummary";
import ChipSelectStep from "../components/OnboardingPage/ChipSelectStep";

const TOTAL_STEPS = ONBOARDING_STEP_FIELDS.length;

const INITIAL_FORM_DATA = ONBOARDING_STEP_FIELDS.reduce(
  (acc, step) => ({ ...acc, [step.field]: "" }),
  {},
);

export default function OnboardingPage() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState(INITIAL_FORM_DATA);
  const [bannerStatus, setBannerStatus] = useState(null); // null | "loading" | "success" | "error"
  const [bannerText, setBannerText] = useState("");
  const [completed, setCompleted] = useState(false);

  // TODO: replace with the real autosave API call
  const simulateSave = (label) => {
    if (!label) return;
    setBannerStatus("loading");
    setBannerText("Saving changes to server...");
    window.setTimeout(() => {
      setBannerStatus("success");
      setBannerText(`Saved: "${label}"`);
      window.setTimeout(() => setBannerStatus(null), 2200);
    }, 700);
  };

  const handleContinue = (field, value) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
    simulateSave(value);
    if (step === TOTAL_STEPS) {
      setCompleted(true);
    } else {
      setStep((s) => s + 1);
    }
  };

  const handlePrevious = () => setStep((s) => Math.max(1, s - 1));

  if (completed) {
    return (
      <div className="mx-auto w-full max-w-[480px] rounded-3xl border border-border bg-surface shadow-sm">
        <OnboardingCompletedSummary
          formData={formData}
          onEdit={() => {
            setCompleted(false);
            setStep(1);
          }}
        />
      </div>
    );
  }

  const currentStepConfig = ONBOARDING_STEP_FIELDS[step - 1];

  return (
    <div className="mx-auto w-full max-w-[480px] rounded-3xl border border-border bg-surface p-6 shadow-sm">
      <StepperHeader currentStep={step} />

      <ActionBanner status={bannerStatus} text={bannerText} className="mb-4" />

      {/* key forces a fresh instance per step, so its internal input/error
          state doesn't leak from one step's field into the next */}
      <ChipSelectStep
        key={currentStepConfig.field}
        stepConfig={currentStepConfig}
        value={formData[currentStepConfig.field]}
        onContinue={handleContinue}
        onPrevious={handlePrevious}
        isFirstStep={step === 1}
      />
    </div>
  );
}
