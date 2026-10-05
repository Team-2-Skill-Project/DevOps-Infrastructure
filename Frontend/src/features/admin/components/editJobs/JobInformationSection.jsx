import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useTranslation } from "react-i18next";

export default function JobInformationSection({ formData, onChange }) {
  const { t } = useTranslation("common");
  return (
    <Card className="bg-white border border-[#ECEAE5] shadow-none rounded-2xl overflow-hidden">
      {/* Header section */}
      <div className="flex items-center justify-between px-6 py-4 border-b border-[#F2F0EC]">
        <h2 className="text-sm font-bold text-[#09090B]">{t("ui.jobInfo.title")}</h2>
        <span className="text-xs text-[#8E8E93]">{t("ui.jobInfo.description")}</span>
      </div>

      <CardContent className="p-6 space-y-5">
        {/* Job Title Field */}
        <div className="space-y-2">
          <Label className="text-xs font-semibold text-[#09090B]">
            {t("ui.jobInfo.jobTitle")} <span className="text-[#DC2626]">*</span>
          </Label>
          <Input
            value={formData?.jobTitle || ""}
            onChange={(e) => onChange("jobTitle", e.target.value)}
            className="h-12 w-full bg-white border-[#E4E4E7] rounded-xl text-sm px-4 focus-visible:ring-[#1D2A44] transition-all"
            placeholder="Senior Frontend Engineer"
          />
        </div>

        {/* Company & Source Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Company Dropdown (Full Width) */}
          <div className="space-y-2">
            <Label className="text-xs font-semibold text-[#09090B]">
              {t("ui.jobInfo.company")} <span className="text-[#DC2626]">*</span>
            </Label>
            <Select
              value={formData?.company || "BrightPath Inc."}
              onValueChange={(val) => onChange("company", val)}
            >
              <SelectTrigger className="h-12 w-full bg-white border-[#E4E4E7] rounded-xl text-sm px-4 focus:ring-[#1D2A44]">
                <SelectValue placeholder={t("ui.jobInfo.selectCompany")} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="BrightPath Inc.">BrightPath Inc.</SelectItem>
                <SelectItem value="TechCorp Solutions">TechCorp Solutions</SelectItem>
                <SelectItem value="MatchIn Labs">MatchIn Labs</SelectItem>
              </SelectContent>
            </Select>
            <p className="text-[11px] text-[#A1A1AA]">{t("ui.jobInfo.linkedCompany")}</p>
          </div>

          {/* Source Dropdown (Full Width) */}
          <div className="space-y-2">
            <Label className="text-xs font-semibold text-[#09090B]">
              {t("ui.jobInfo.source")} <span className="text-[#A1A1AA] font-normal">{t("ui.jobInfo.optional")}</span>
            </Label>
            <Select
              value={formData?.source || "Internal"}
              onValueChange={(val) => onChange("source", val)}
            >
              <SelectTrigger className="h-12 w-full bg-white border-[#E4E4E7] rounded-xl text-sm px-4 focus:ring-[#1D2A44]">
                <SelectValue placeholder={t("ui.jobInfo.selectSource")} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Internal">Internal</SelectItem>
                <SelectItem value="External">External</SelectItem>
                <SelectItem value="LinkedIn">LinkedIn</SelectItem>
                <SelectItem value="Referral">Referral</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}