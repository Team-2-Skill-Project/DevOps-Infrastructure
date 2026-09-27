import React from "react";
import { Link } from "react-router-dom";
import { FileText } from "lucide-react";
import { useLocalizedPath } from "@/utils/routes";

export default function ProfileCompletionCard({
  completionPercentage = 85,
  linkText = "Manage your CV file",
  cvPath = "/dashboard/cv-management",
  className = "",
}) {
  const localizedPath = useLocalizedPath();

  return (
    <div
      className={`w-full md:w-72 bg-amber-50/50 border border-amber-100 rounded-xl p-4 space-y-3 ${className}`}
    >
      <div className="flex justify-between items-center text-xs font-semibold">
        <span className="text-gray-700">Profile Completion</span>
        <span className="text-slate-800">{completionPercentage}%</span>
      </div>

      <div className="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
        <div
          className="bg-slate-800 h-full rounded-full transition-all duration-500"
          style={{ width: `${completionPercentage}%` }}
        />
      </div>

      <Link
        to={localizedPath(cvPath)}
        className="flex items-center gap-2 text-xs font-semibold text-amber-900 hover:underline pt-1 cursor-pointer"
      >
        <FileText className="w-3.5 h-3.5" />
        <span>{linkText}</span>
      </Link>
    </div>
  );
}
