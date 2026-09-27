import React, { useState } from "react";
import { Pencil } from "lucide-react";
import TabSaveActions from "./TabSaveActions";


export default function PersonalInfoTab() {
  const [isEditing, setIsEditing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);

  const [userInfo, setUserInfo] = useState({
    titleRole: "Senior Frontend Engineer",
    company: "Cairo University",
  });

  const [formData, setFormData] = useState({ ...userInfo });

  const handleOpenEdit = () => {
    setFormData({ ...userInfo });
    setIsEditing(true);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSaveData = async () => {
    setIsSaving(true);
    
    setTimeout(() => {
      setUserInfo({ ...formData });
      setIsSaving(false);
      setIsEditing(false); 
    }, 1500);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-bold text-slate-900 tracking-tight">
          Personal Information
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
        <div className="bg-[#FAF8F5]/80 border border-gray-100/60 p-4 rounded-2xl space-y-1">
          <span className="text-xs font-medium text-slate-400 block">
            Title / Role
          </span>
          <p className="text-sm font-bold text-slate-900">
            {userInfo.titleRole}
          </p>
        </div>

        <div className="bg-[#FAF8F5]/80 border border-gray-100/60 p-4 rounded-2xl space-y-1">
          <span className="text-xs font-medium text-slate-400 block">
            Company / Institution
          </span>
          <p className="text-sm font-bold text-slate-900">
            {userInfo.company || "—"}
          </p>
        </div>
      </div>

      <TabSaveActions
        isOpen={isEditing}
        onClose={() => setIsEditing(false)}
        title="Add / Edit Personal Information"
        confirmLabel="Save"
        isLoading={isSaving}
        loadingLabel="Saving..."
        onConfirm={handleSaveData}
      >
        <div className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Title / Role *
            </label>
            <input
              type="text"
              name="titleRole"
              value={formData.titleRole}
              onChange={handleChange}
              placeholder="e.g. Senior Frontend Engineer"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Company / Institution
            </label>
            <input
              type="text"
              name="company"
              value={formData.company}
              onChange={handleChange}
              placeholder="e.g. Cairo University"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>
        </div>
      </TabSaveActions>
    </div>
  );
}