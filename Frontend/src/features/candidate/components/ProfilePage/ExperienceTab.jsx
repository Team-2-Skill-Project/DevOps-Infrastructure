import React, { useState } from "react";
import { Plus } from "lucide-react";
import ItemCard from "./shared/ItemCard";
import TabSaveActions from "./TabSaveActions";

const DEFAULT_EXPERIENCES = [
  {
    id: 1,
    title: "Senior Frontend Developer",
    company: "TechCorp Inc.",
    type: "Full-time",
    startDate: "January 2022",
    endDate: "Present",
  },
];

export default function ExperienceTab({ initialExperiences = DEFAULT_EXPERIENCES }) {
  const [experiences, setExperiences] = useState(initialExperiences);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [editingId, setEditingId] = useState(null); 
  const [formData, setFormData] = useState({
    title: "",
    company: "",
    type: "Full-time",
    startDate: "",
    endDate: "",
  });

  const [isDeleteOpen, setIsDeleteOpen] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [itemToDelete, setItemToDelete] = useState(null);

  const handleOpenAdd = () => {
    setEditingId(null);
    setFormData({
      title: "",
      company: "",
      type: "Full-time",
      startDate: "",
      endDate: "",
    });
    setIsFormOpen(true);
  };

  const handleOpenEdit = (exp) => {
    setEditingId(exp.id);
    setFormData({
      title: exp.title,
      company: exp.company,
      type: exp.type || "Full-time",
      startDate: exp.startDate || "",
      endDate: exp.endDate || "",
    });
    setIsFormOpen(true);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = () => {
    if (!formData.title.trim()) return;

    setIsSaving(true);
    setTimeout(() => {
      if (editingId) {
        setExperiences((prev) =>
          prev.map((item) =>
            item.id === editingId ? { ...item, ...formData } : item
          )
        );
      } else {
        const newExp = {
          id: Date.now(),
          ...formData,
        };
        setExperiences((prev) => [...prev, newExp]);
      }

      setIsSaving(false);
      setIsFormOpen(false);
    }, 1000);
  };

  const handleOpenDelete = (exp) => {
    setItemToDelete(exp);
    setIsDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!itemToDelete) return;

    setIsDeleting(true);
    setTimeout(() => {
      setExperiences((prev) => prev.filter((item) => item.id !== itemToDelete.id));
      setIsDeleting(false);
      setIsDeleteOpen(false);
      setItemToDelete(null);
    }, 1000);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-bold text-slate-900 tracking-tight">Work Experience</h3>
        <button
          onClick={handleOpenAdd}
          className="inline-flex items-center gap-2 bg-[#1E325C] hover:bg-[#162545] text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-sm active:scale-95 cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Experience</span>
        </button>
      </div>

      <div className="space-y-4">
        {experiences.map((exp) => (
          <ItemCard
            key={exp.id}
            title={exp.title}
            subtitle={`${exp.company} • ${exp.type}`}
            date={`${exp.startDate} - ${exp.endDate}`}
            onEdit={() => handleOpenEdit(exp)}
            onDelete={() => handleOpenDelete(exp)}
          />
        ))}
      </div>

      <TabSaveActions
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title={editingId ? "Edit Work Experience" : "Add Work Experience"}
        confirmLabel="Save"
        isLoading={isSaving}
        loadingLabel="Saving..."
        onConfirm={handleSave}
      >
        <div className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">Title / Role *</label>
            <input
              type="text"
              name="title"
              value={formData.title}
              onChange={handleChange}
              placeholder="e.g. Senior Frontend Engineer"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">Company / Institution *</label>
            <input
              type="text"
              name="company"
              value={formData.company}
              onChange={handleChange}
              placeholder="e.g. TechCorp Inc."
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-gray-700 block">Start Date</label>
              <input
                type="text"
                name="startDate"
                value={formData.startDate}
                onChange={handleChange}
                placeholder="e.g. Jan 2022"
                className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
              />
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-bold text-gray-700 block">End Date</label>
              <input
                type="text"
                name="endDate"
                value={formData.endDate}
                onChange={handleChange}
                placeholder="e.g. Present"
                className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
              />
            </div>
          </div>
        </div>
      </TabSaveActions>

      <TabSaveActions
        isOpen={isDeleteOpen}
        onClose={() => setIsDeleteOpen(false)}
        description={
          itemToDelete
            ? `Are you sure you want to delete ${itemToDelete.title} at ${itemToDelete.company}?`
            : "Are you sure you want to delete this item?"
        }
        variant="danger"
        confirmLabel="Delete"
        isLoading={isDeleting}
        loadingLabel="Deleting..."
        onConfirm={handleConfirmDelete}
      />
    </div>
  );
}