import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Plus, Trash2 } from "lucide-react";
import { useTranslation } from "react-i18next";

export default function ResponsibilitiesSection({ formData, onChange }) {
  const { t } = useTranslation("common");
  const [newItem, setNewItem] = useState("");
  
  const responsibilities = Array.isArray(formData?.responsibilities)
    ? formData.responsibilities
    : (formData?.responsibilities || "")
        .split("\n")
        .map((item) => item.replace(/^•\s*/, "").trim())
        .filter(Boolean);

  const handleUpdate = (newList) => {
    onChange("responsibilities", newList.map((item) => `• ${item}`).join("\n"));
  };

  const handleAdd = () => {
    if (!newItem.trim()) return;
    handleUpdate([...responsibilities, newItem.trim()]);
    setNewItem("");
  };

  const handleRemove = (index) => {
    handleUpdate(responsibilities.filter((_, i) => i !== index));
  };

  return (
    <Card className="bg-white border border-[#ECEAE5] shadow-none rounded-xl">
      <CardHeader>
        <CardTitle className="text-slate-900">{t("ui.responsibilities.title")}</CardTitle>
        <CardDescription className="text-slate-500">{t("ui.responsibilities.description")}</CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex gap-2">
          <Input
            placeholder={t("ui.responsibilities.placeholder")}
            value={newItem}
            onChange={(e) => setNewItem(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && (e.preventDefault(), handleAdd())}
            className="border-[#E4E4E7] focus-visible:ring-[#1D2A44]"
          />
          <Button type="button" onClick={handleAdd} className="bg-[#1D2A44] hover:bg-[#162034] text-white">
            <Plus className="h-4 w-4 mr-1" /> {t("ui.responsibilities.add")}
          </Button>
        </div>

        <ul className="space-y-2">
          {responsibilities.map((item, index) => (
            <li
              key={index}
              className="flex items-center justify-between rounded-md border border-[#ECEAE5] p-3 bg-slate-50/50 text-sm text-slate-800"
            >
              <span>{item}</span>
              <Button
                variant="ghost"
                size="icon"
                type="button"
                className="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50"
                onClick={() => handleRemove(index)}
              >
                <Trash2 className="h-4 w-4" />
              </Button>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  );
}