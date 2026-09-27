import * as React from "react";

import { RegisterFormView } from "./RegisterFormView";
import { OtpView } from "./OtpView";
import { useState } from "react";

/**
 * RegisterStep
 * Wraps two internal phases — "form" then "otp" — as a single wizard
 * step, since verifying the email is tightly coupled to the account
 * form that was just submitted.
 *
 * onComplete(data)      — called once the OTP is verified.
 *   data = { fullName, email, password, otpCode }
 * onPhaseChange(phase)  — lets the parent wizard advance a shared
 *   progress bar when this step moves between "form" and "otp".
 */
export function RegisterStep({ onComplete, onPhaseChange }) {
  const [phase, setPhase] = useState("form"); // "form" | "otp"
  const [registerData, setRegisterData] = useState(null);

  const goToPhase = (next) => {
    setPhase(next);
    onPhaseChange?.(next);
  };

  const handleRegisterSubmit = (data) => {
    // TODO: call the register API here, then move to OTP on success
    setRegisterData(data);
    goToPhase("otp");
  };

  const handleOtpVerified = async (code) => {
    // TODO: call the verify-otp API here
    onComplete?.({ ...registerData, otpCode: code });
  };

  const handleResend = () => {
    // TODO: call the resend-otp API here
  };

  if (phase === "otp") {
    return (
      <OtpView
        email={registerData?.email}
        onVerified={handleOtpVerified}
        onBack={() => goToPhase("form")}
        onResend={handleResend}
      />
    );
  }

  return <RegisterFormView onSubmit={handleRegisterSubmit} />;
}
