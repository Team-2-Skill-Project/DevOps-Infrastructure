import AuthLayout from "@/components/layouts/AuthLayout";
import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { StepProgress } from "@/features/auth/components/RegisterPage/step-progress";
import { RegisterStep } from "@/features/auth/components/RegisterPage/RegisterStep";
import { CvUploadStep } from "@/features/auth/components/RegisterPage/CvUploadStep";
import { ProfileCompletionStep } from "@/features/auth/components/RegisterPage/Profilecompletionstep";
import { BriefcaseBusiness } from "lucide-react";
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";

export default function RegisterPage() {
  const navigate = useNavigate();
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("common");

  const [step, setStep] = useState(0);
  const [registerData, setRegisterData] = useState(null); // { fullName, email, password, otpCode }
  const [cvFile, setCvFile] = useState(null);

  const handleAccountPhaseChange = (phase) => {
    setStep(phase === "otp" ? 1 : 1);
  };

  const handleAccountComplete = (data) => {
    // TODO: account + otp were already verified inside RegisterStep;
    // this is just where the wizard advances once that's done.
    setRegisterData(data);
    setStep(2);
  };

  const handleCvComplete = (file) => {
    setCvFile(file);
    setStep(3);
  };

  const handleCvSkip = () => {
    // "Browse Jobs" bypasses profile completion entirely.
    navigate(localizedPath("/dashboard/jobs"));
  };

  const handleProfileBack = () => {
    setStep(0);
  };

  const handleProfileFinish = (profileData) => {
    // TODO: submit { ...registerData, cvFile, ...profileData } to the API
    navigate(localizedPath("/dashboard"));
  };

  return (
    <AuthLayout
      sidePanel={{
        title: t("auth.side.title"),
        description: t("auth.side.description"),
        imageSrc:
          "https://images.unsplash.com/photo-1633114128814-11fac33f707b?fm=jpg&q=80&w=1400&auto=format&fit=crop",
        imageAlt: "A person working on a laptop",
        badgeText: t("auth.side.badge"),
        badgeIcon: <BriefcaseBusiness width={13} height={13} />,
      }}
      additional={
        <StepProgress
          steps={[
            t("auth.register.steps", { returnObjects: true }),
          ]}
          currentStep={step}
        />
      }
      cardClassName={"justify-start"}
      footer={
        <p className="mx-auto w-full max-w-2xl px-6 pb-4 text-center text-[11.5px] leading-4 text-muted">
          {t("auth.register.haveAccount")}{" "}
          <Link
            to={localizedPath("/auth/login")}
            className="border-b border-border text-ink/80 hover:text-ink"
          >
            {t("auth.register.signIn")}
          </Link>
        </p>
      }
    >
      {step <= 1 && (
        <RegisterStep
          onPhaseChange={handleAccountPhaseChange}
          onComplete={handleAccountComplete}
        />
      )}

      {step === 2 && (
        <CvUploadStep onComplete={handleCvComplete} onSkip={handleCvSkip} />
      )}

      {step === 3 && (
        <ProfileCompletionStep
          cvFileName={cvFile?.name}
          onBack={handleProfileBack}
          onFinish={handleProfileFinish}
        />
      )}
    </AuthLayout>
  );
}
