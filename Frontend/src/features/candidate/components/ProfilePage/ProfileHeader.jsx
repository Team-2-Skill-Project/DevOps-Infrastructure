import React from "react";
import { CheckCircle2 } from "lucide-react";
import ProfileCompletionCard from "./ProfileCompletionCard";

export default function ProfileHeader({
  name = "Ahmed Mahmoud",
  title = "Senior Frontend Engineer • Cairo, Egypt",
  initials = "AM",
  completionPercentage = 85,
  cvPath = "/dashboard/cv-management",
  verifiedText = "Verified & AI-Extracted CV",
  className = "",
}) {
  return (
    <div
      className={`bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 ${className}`}
    >
      <div className="flex items-center gap-4">
        <div className="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center text-xl font-bold text-slate-700">
          {initials}
        </div>
        <div className="space-y-1">
          <h1 className="text-xl font-bold text-gray-900">{name}</h1>
          <p className="text-sm text-gray-500 font-medium">{title}</p>
          <div className="flex items-center gap-1.5 text-xs text-emerald-600 font-medium pt-0.5">
            <CheckCircle2 className="w-4 h-4" />
            <span>{verifiedText}</span>
          </div>
        </div>
      </div>

      <ProfileCompletionCard
        completionPercentage={completionPercentage}
        cvPath={cvPath}
      />
    </div>
  );
}
