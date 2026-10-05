import { useState } from "react";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Label } from "@/components/ui/label";
import { X } from "lucide-react";
import { useTranslation } from "react-i18next";

export default function SkillsSection({ formData, onChange }) {
  const { t } = useTranslation("common");
  const [reqSkills, setReqSkills] = useState(
    formData?.requiredSkills || ["React", "TypeScript", "CSS / Tailwind", "Git"]
  );
  const [prefSkills, setPrefSkills] = useState(
    formData?.preferredSkills || ["Next.js", "GraphQL", "Framer Motion"]
  );

  const [reqInput, setReqInput] = useState("");
  const [prefInput, setPrefInput] = useState("");

  const addReqSkill = (e) => {
    if (e.key === "Enter" && reqInput.trim()) {
      e.preventDefault();
      if (!reqSkills.includes(reqInput.trim())) {
        const updated = [...reqSkills, reqInput.trim()];
        setReqSkills(updated);
        onChange?.("requiredSkills", updated);
      }
      setReqInput("");
    }
  };

  const removeReqSkill = (skill) => {
    const updated = reqSkills.filter((s) => s !== skill);
    setReqSkills(updated);
    onChange?.("requiredSkills", updated);
  };

  const addPrefSkill = (e) => {
    if (e.key === "Enter" && prefInput.trim()) {
      e.preventDefault();
      if (!prefSkills.includes(prefInput.trim())) {
        const updated = [...prefSkills, prefInput.trim()];
        setPrefSkills(updated);
        onChange?.("preferredSkills", updated);
      }
      setPrefInput("");
    }
  };

  const removePrefSkill = (skill) => {
    const updated = prefSkills.filter((s) => s !== skill);
    setPrefSkills(updated);
    onChange?.("preferredSkills", updated);
  };

  return (
    <Card className="bg-white border border-[#ECEAE5] shadow-none rounded-2xl overflow-hidden">
      <div className="flex items-center justify-between px-6 py-4 border-b border-[#F2F0EC]">
        <h2 className="text-sm font-bold text-[#09090B]">{t("ui.skills.title")}</h2>
        <span className="text-xs text-[#8E8E93]">{t("ui.skills.description")}</span>
      </div>

      <CardContent className="p-6 space-y-5">
        {/* Required Skills */}
        <div className="space-y-2">
          <Label className="text-xs font-semibold text-[#09090B]">
            {t("ui.skills.required")} <span className="text-[#DC2626]">*</span>
          </Label>
          <div className="flex flex-wrap items-center gap-2 min-h-[48px] p-2 bg-white border border-[#E4E4E7] rounded-xl focus-within:ring-1 focus-within:ring-[#1D2A44]">
            {reqSkills.map((skill) => (
              <Badge
                key={skill}
                className="bg-[#F4F4F5] hover:bg-[#E4E4E7] text-[#09090B] border-none px-3 py-1 text-xs font-medium rounded-xl flex items-center gap-1.5 shadow-none"
              >
                {skill}
                <button
                  type="button"
                  onClick={() => removeReqSkill(skill)}
                  className="hover:text-[#DC2626] transition-colors cursor-pointer"
                >
                  <X className="h-3.5 w-3.5 text-[#09090B]" />
                </button>
              </Badge>
            ))}
            <input
              type="text"
              placeholder={t("ui.skills.placeholder")}
              value={reqInput}
              onChange={(e) => setReqInput(e.target.value)}
              onKeyDown={addReqSkill}
              className="flex-1 bg-transparent border-none outline-none text-sm px-2 text-[#09090B] placeholder:text-[#A1A1AA] min-w-[180px]"
            />
          </div>
        </div>

        {/* Preferred Skills */}
        <div className="space-y-2">
          <Label className="text-xs font-semibold text-[#09090B]">
            {t("ui.skills.preferred")} <span className="text-[#A1A1AA] font-normal">{t("ui.skills.optional")}</span>
          </Label>
          <div className="flex flex-wrap items-center gap-2 min-h-[48px] p-2 bg-white border border-[#E4E4E7] rounded-xl focus-within:ring-1 focus-within:ring-[#1D2A44]">
            {prefSkills.map((skill) => (
              <Badge
                key={skill}
                className="bg-[#F4F4F5] hover:bg-[#E4E4E7] text-[#09090B] border-none px-3 py-1 text-xs font-medium rounded-xl flex items-center gap-1.5 shadow-none"
              >
                {skill}
                <button
                  type="button"
                  onClick={() => removePrefSkill(skill)}
                  className="hover:text-[#DC2626] transition-colors cursor-pointer"
                >
                  <X className="h-3.5 w-3.5 text-[#09090B]" />
                </button>
              </Badge>
            ))}
            <input
              type="text"
              placeholder={t("ui.skills.placeholder")}
              value={prefInput}
              onChange={(e) => setPrefInput(e.target.value)}
              onKeyDown={addPrefSkill}
              className="flex-1 bg-transparent border-none outline-none text-sm px-2 text-[#09090B] placeholder:text-[#A1A1AA] min-w-[180px]"
            />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}