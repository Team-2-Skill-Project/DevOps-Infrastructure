import { Card, CardContent } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { useTranslation } from "react-i18next";

export default function DescriptionSection({ formData, onChange }) {
  const { t } = useTranslation("common");
  return (
    <Card className="bg-white border border-[#ECEAE5] shadow-none rounded-2xl overflow-hidden">
      {/* Header */}
      <div className="flex items-center justify-between px-6 py-4 border-b border-[#F2F0EC]">
        <h2 className="text-sm font-bold text-[#09090B]">{t("ui.jobDescription.title")}</h2>
        <span className="text-xs text-[#8E8E93]">{t("ui.jobDescription.summary")}</span>
      </div>

      <CardContent className="p-6 space-y-2">
        <Label className="text-xs font-semibold text-[#09090B]">
          {t("ui.jobDescription.field")} <span className="text-[#DC2626]">*</span>
        </Label>
        <Textarea
          rows={4}
          value={formData?.description || ""}
          onChange={(e) => onChange("description", e.target.value)}
          className="bg-white border-[#E4E4E7] rounded-xl text-sm focus-visible:ring-[#1D2A44] leading-relaxed resize-none"
        />
      </CardContent>
    </Card>
  );
}