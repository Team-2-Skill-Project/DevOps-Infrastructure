import React, { useState } from "react";
import { Plus } from "lucide-react";
import ItemCard from "./shared/ItemCard";
import TabSaveActions from "./TabSaveActions";

const DEFAULT_EDUCATION = [
  {
    id: 1,
    degree: "Bachelor of Computer Science",
    institution: "Cairo University",
    startYear: "2017",
    endYear: "2021",
  },
];

export default function EducationTab({ initialEducation = DEFAULT_EDUCATION }) {
  const [educationList, setEducationList] = useState(initialEducation);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    degree: "",
    institution: "",
    startYear: "",
    endYear: "",
  });

  const [isDeleteOpen, setIsDeleteOpen] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [itemToDelete, setItemToDelete] = useState(null);

  const handleOpenAdd = () => {
    setEditingId(null);
    setFormData({
      degree: "",
      institution: "",
      startYear: "",
      endYear: "",
    });
    setIsFormOpen(true);
  };

  const handleOpenEdit = (edu) => {
    setEditingId(edu.id);
    setFormData({
      degree: edu.degree || "",
      institution: edu.institution || "",
      startYear: edu.startYear || "",
      endYear: edu.endYear || "",
    });
    setIsFormOpen(true);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = () => {
    if (!formData.degree.trim()) return;

    setIsSaving(true);
    setTimeout(() => {
      if (editingId) {
        setEducationList((prev) =>
          prev.map((item) =>
            item.id === editingId ? { ...item, ...formData } : item,
          ),
        );
      } else {
        const newEdu = {
          id: Date.now(),
          ...formData,
        };
        setEducationList((prev) => [...prev, newEdu]);
      }

      setIsSaving(false);
      setIsFormOpen(false);
    }, 1000);
  };

  const handleOpenDelete = (edu) => {
    setItemToDelete(edu);
    setIsDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!itemToDelete) return;

    setIsDeleting(true);
    setTimeout(() => {
      setEducationList((prev) =>
        prev.filter((item) => item.id !== itemToDelete.id),
      );
      setIsDeleting(false);
      setIsDeleteOpen(false);
      setItemToDelete(null);
    }, 1000);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-bold text-slate-900 tracking-tight">
          Education & Qualifications
        </h3>
        <button
          onClick={handleOpenAdd}
          className="inline-flex items-center gap-2 bg-[#1E325C] hover:bg-[#162545] text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-sm active:scale-95 cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Education</span>
        </button>
      </div>

      <div className="space-y-4">
        {educationList.map((edu) => (
          <ItemCard
            key={edu.id}
            title={edu.degree}
            subtitle={edu.institution}
            date={`${edu.startYear} - ${edu.endYear}`}
            onEdit={() => handleOpenEdit(edu)}
            onDelete={() => handleOpenDelete(edu)}
          />
        ))}
      </div>

      <TabSaveActions
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title={editingId ? "Edit Education" : "Add Education"}
        confirmLabel="Save"
        isLoading={isSaving}
        loadingLabel="Saving..."
        onConfirm={handleSave}
      >
        <div className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Degree / Field of Study *
            </label>
            <input
              type="text"
              name="degree"
              value={formData.degree}
              onChange={handleChange}
              placeholder="e.g. Bachelor of Computer Science"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              School / Institution *
            </label>
            <input
              type="text"
              name="institution"
              value={formData.institution}
              onChange={handleChange}
              placeholder="e.g. Cairo University"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-gray-700 block">
                Start Year
              </label>
              <input
                type="text"
                name="startYear"
                value={formData.startYear}
                onChange={handleChange}
                placeholder="e.g. 2017"
                className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
              />
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-bold text-gray-700 block">
                End Year
              </label>
              <input
                type="text"
                name="endYear"
                value={formData.endYear}
                onChange={handleChange}
                placeholder="e.g. 2021"
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
            ? `Are you sure you want to delete ${itemToDelete.institution} Education?`
            : "Are you sure you want to delete this education item?"
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
