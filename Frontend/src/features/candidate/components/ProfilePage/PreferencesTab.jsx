import React, { useState } from "react";
import { Pencil } from "lucide-react";
import TabSaveActions from "./TabSaveActions";


export default function CareerPreferencesTab() {
  const [isEditing, setIsEditing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);

  const [preferences, setPreferences] = useState({
    targetJobTitle: "Lead Frontend Architect",
    preferredWorkEnv: "Remote",
  });

  const [formData, setFormData] = useState({ ...preferences });

  const handleOpenEdit = () => {
    setFormData({ ...preferences });
    setIsEditing(true);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSaveData = () => {
    setIsSaving(true);
    setTimeout(() => {
      setPreferences({ ...formData });
      setIsSaving(false);
      setIsEditing(false);
    }, 1000);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-bold text-slate-900 tracking-tight">
          Career Preferences (Target Role)
        </h3>

        <button
          onClick={handleOpenEdit}
          className="inline-flex items-center gap-2 bg-[#1E325C] hover:bg-[#162545] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-sm active:scale-95 cursor-pointer"
        >
          <Pencil className="w-3.5 h-3.5" />
          <span>Edit</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Target Job Title */}
        <div className="bg-[#FAF8F5]/80 border border-gray-100/60 p-4 rounded-2xl space-y-1">
          <span className="text-xs font-medium text-slate-400 block">
            Target Job Title
          </span>
          <p className="text-sm font-bold text-slate-900">
            {preferences.targetJobTitle || "—"}
          </p>
        </div>

        <div className="bg-[#FAF8F5]/80 border border-gray-100/60 p-4 rounded-2xl space-y-1">
          <span className="text-xs font-medium text-slate-400 block">
            Preferred Work Environment
          </span>
          <p className="text-sm font-bold text-slate-900">
            {preferences.preferredWorkEnv || "—"}
          </p>
        </div>
      </div>

      <TabSaveActions
        isOpen={isEditing}
        onClose={() => setIsEditing(false)}
        title="Edit Career Preferences"
        confirmLabel="Save"
        isLoading={isSaving}
        loadingLabel="Saving..."
        onConfirm={handleSaveData}
      >
        <div className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Target Job Title *
            </label>
            <input
              type="text"
              name="targetJobTitle"
              value={formData.targetJobTitle}
              onChange={handleChange}
              placeholder="e.g. Lead Frontend Architect"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Preferred Work Environment
            </label>
            <div className="relative">
              <select
                name="preferredWorkEnv"
                value={formData.preferredWorkEnv}
                onChange={handleChange}
                className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all appearance-none cursor-pointer pe-10"
              >
                <option value="Remote">Remote</option>
                <option value="Hybrid">Hybrid</option>
                <option value="On-site">On-site</option>
              </select>
              <div className="absolute inset-y-0 inset-e-0 flex items-center px-4 pointer-events-none text-gray-500">
                <svg
                  className="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </TabSaveActions>
    </div>
  );
}
