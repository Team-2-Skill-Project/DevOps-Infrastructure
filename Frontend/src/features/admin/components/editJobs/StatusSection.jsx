import { Card, CardContent } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Info } from "lucide-react";
import { useTranslation } from "react-i18next";

export default function StatusSection({ formData, onChange }) {
  const { t } = useTranslation("common");
  const statuses = [
    { name: "Open", labelKey: "ui.status.open", color: "bg-[#2E7D32]" },
    { name: "Draft", labelKey: "ui.status.draft", color: "bg-[#D97706]" },
    { name: "Closed", labelKey: "ui.status.closed", color: "bg-[#71717A]" },
    { name: "Suspended", labelKey: "ui.status.suspended", color: "bg-[#DC2626]" },
  ];

  const currentStatus = formData?.status || "Open";

  return (
    <Card className="bg-white border border-[#ECEAE5] shadow-none rounded-2xl overflow-hidden">
      <div className="flex items-center justify-between px-6 py-4 border-b border-[#F2F0EC]">
        <h2 className="text-sm font-bold text-[#09090B]">{t("ui.status.title")}</h2>
        <span className="text-xs text-[#8E8E93]">{t("ui.status.description")}</span>
      </div>

      <CardContent className="p-6 space-y-5">
        <div className="space-y-2">
          <Label className="text-xs font-semibold text-[#09090B]">
            {t("ui.status.jobStatus")} <span className="text-[#DC2626]">*</span>
          </Label>
          <div className="flex flex-wrap gap-3">
            {statuses.map((st) => {
              const active = currentStatus === st.name;
              return (
                <button
                  key={st.name}
                  type="button"
                  onClick={() => onChange?.("status", st.name)}
                  className={`flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer border ${
                    active
                      ? "bg-[#F0F4F8] border-[#1D2A44] text-[#1D2A44] shadow-sm"
                      : "bg-white border-[#E4E4E7] text-[#09090B] hover:bg-[#F4F4F5]"
                  }`}
                >
                  <span className={`h-2 w-2 rounded-full ${st.color}`} />
                  {t(st.labelKey)}
                </button>
              );
            })}
          </div>
        </div>

        {/* Info Banner */}
        <div className="flex items-start gap-3 p-4 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-xs text-[#64748B] leading-relaxed">
          <Info className="h-4 w-4 text-[#64748B] shrink-0 mt-0.5" />
          <span>
            {t("ui.status.auditStart")} {" "}
            <strong className="font-semibold text-[#0F172A]">{t("ui.status.auditLogs")}</strong>{" "}
            {t("ui.status.auditEnd")}
          </span>
        </div>
      </CardContent>
    </Card>
  );
}