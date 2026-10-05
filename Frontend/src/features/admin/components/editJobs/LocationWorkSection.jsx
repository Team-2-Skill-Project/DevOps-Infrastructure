import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useTranslation } from "react-i18next";

export default function LocationWorkDetailsSection({ formData, onChange }) {
  const { t } = useTranslation("common");
  const workModes = [
    ["On-site", "ui.locationWork.onSite"],
    ["Hybrid", "ui.locationWork.hybrid"],
    ["Remote", "ui.locationWork.remote"],
  ];
  const jobTypes = [
    ["Full-time", "ui.locationWork.fullTime"],
    ["Part-time", "ui.locationWork.partTime"],
    ["Contract", "ui.locationWork.contract"],
    ["Internship", "ui.locationWork.internship"],
  ];

  return (
    <Card className="bg-white border border-[#ECEAE5] shadow-none rounded-2xl overflow-hidden">
      <div className="flex items-center justify-between px-6 py-4 border-b border-[#F2F0EC]">
        <h2 className="text-sm font-bold text-[#09090B]">{t("ui.locationWork.title")}</h2>
      </div>

      <CardContent className="p-6 space-y-6">
        {/* Location & Experience Level Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Location Input */}
          <div className="space-y-2">
            <Label className="text-xs font-semibold text-[#09090B]">
              {t("ui.locationWork.location")} <span className="text-[#DC2626]">*</span>
            </Label>
            <Input
              value={formData?.location || "Cairo, Egypt"}
              onChange={(e) => onChange?.("location", e.target.value)}
              className="h-12 w-full bg-white border-[#E4E4E7] rounded-xl text-sm px-4 focus-visible:ring-[#1D2A44]"
            />
          </div>

          {/* Experience Level Dropdown (Full Width & h-12) */}
          <div className="space-y-2">
            <Label className="text-xs font-semibold text-[#09090B]">
              {t("ui.locationWork.experience")} <span className="text-[#DC2626]">*</span>
            </Label>
            <Select
              value={formData?.experienceLevel || "Senior"}
              onValueChange={(val) => onChange?.("experienceLevel", val)}
            >
              <SelectTrigger className="h-12 w-full bg-white border-[#E4E4E7] rounded-xl text-sm px-4 focus:ring-[#1D2A44]">
                <SelectValue placeholder={t("ui.locationWork.selectLevel")} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Junior">{t("ui.locationWork.junior")}</SelectItem>
                <SelectItem value="Mid-Level">{t("ui.locationWork.midLevel")}</SelectItem>
                <SelectItem value="Senior">{t("ui.locationWork.senior")}</SelectItem>
                <SelectItem value="Lead">{t("ui.locationWork.lead")}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        {/* Work Mode Pill Buttons */}
        <div className="space-y-2">
          <Label className="text-xs font-semibold text-[#09090B]">
            {t("ui.locationWork.workMode")} <span className="text-[#DC2626]">*</span>
          </Label>
          <div className="flex flex-wrap gap-2">
            {workModes.map(([mode, labelKey]) => {
              const active = (formData?.workMode || "Hybrid") === mode;
              return (
                <button
                  key={mode}
                  type="button"
                  onClick={() => onChange?.("workMode", mode)}
                  className={`px-5 py-2.5 rounded-full text-xs font-medium transition-colors cursor-pointer border ${
                    active
                      ? "bg-[#1D2A44] text-white border-[#1D2A44]"
                      : "bg-white text-[#09090B] border-[#E4E4E7] hover:bg-[#F4F4F5]"
                  }`}
                >
                  {t(labelKey)}
                </button>
              );
            })}
          </div>
        </div>

        {/* Job Type Pill Buttons */}
        <div className="space-y-2">
          <Label className="text-xs font-semibold text-[#09090B]">
            {t("ui.locationWork.jobType")} <span className="text-[#DC2626]">*</span>
          </Label>
          <div className="flex flex-wrap gap-2">
            {jobTypes.map(([type, labelKey]) => {
              const active = (formData?.jobType || "Full-time") === type;
              return (
                <button
                  key={type}
                  type="button"
                  onClick={() => onChange?.("jobType", type)}
                  className={`px-5 py-2.5 rounded-full text-xs font-medium transition-colors cursor-pointer border ${
                    active
                      ? "bg-[#1D2A44] text-white border-[#1D2A44]"
                      : "bg-white text-[#09090B] border-[#E4E4E7] hover:bg-[#F4F4F5]"
                  }`}
                >
                  {t(labelKey)}
                </button>
              );
            })}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}