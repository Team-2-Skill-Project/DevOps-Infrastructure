import React, { useState } from "react";
import { Plus, ExternalLink } from "lucide-react";
import ItemCard from "./shared/ItemCard";
import TabSaveActions from "./TabSaveActions";

const DEFAULT_PROJECTS = [
  {
    id: 1,
    title: "E-Commerce Platform",
    description: "Built a responsive online store with React and Tailwind CSS.",
    link: "https://github.com/example/project",
    isExtracted: true,
  },
  {
    id: 2,
    title: "Portfolio Website",
    description: "Personal profile dashboard showcasing skills and experiences.",
    link: "",
    isExtracted: false,
  },
];

export default function ProjectsTab({ initialProjects = DEFAULT_PROJECTS }) {
  const [projects, setProjects] = useState(initialProjects);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    title: "",
    description: "",
    link: "",
  });

  const [isDeleteOpen, setIsDeleteOpen] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [itemToDelete, setItemToDelete] = useState(null);

  const handleOpenAdd = () => {
    setEditingId(null);
    setFormData({
      title: "",
      description: "",
      link: "",
    });
    setIsFormOpen(true);
  };

  const handleOpenEdit = (project) => {
    setEditingId(project.id);
    setFormData({
      title: project.title || "",
      description: project.description || "",
      link: project.link || "",
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
        setProjects((prev) =>
          prev.map((item) =>
            item.id === editingId ? { ...item, ...formData } : item
          )
        );
      } else {
        const newProject = {
          id: Date.now(),
          ...formData,
          isExtracted: false,
        };
        setProjects((prev) => [...prev, newProject]);
      }

      setIsSaving(false);
      setIsFormOpen(false);
    }, 1000);
  };

  const handleOpenDelete = (project) => {
    setItemToDelete(project);
    setIsDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!itemToDelete) return;

    setIsDeleting(true);
    setTimeout(() => {
      setProjects((prev) => prev.filter((item) => item.id !== itemToDelete.id));
      setIsDeleting(false);
      setIsDeleteOpen(false);
      setItemToDelete(null);
    }, 1000);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-bold text-slate-900 tracking-tight">Projects</h3>
        <button
          onClick={handleOpenAdd}
          className="inline-flex items-center gap-2 bg-[#1E325C] hover:bg-[#162545] text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-sm active:scale-95 cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Project</span>
        </button>
      </div>

      <div className="space-y-4">
        {projects.map((project) => (
          <ItemCard
            key={project.id}
            title={
              <div className="flex items-center gap-2">
                <span className="font-bold text-slate-900">{project.title}</span>
                {project.link && (
                  <a
                    href={project.link}
                    target="_blank"
                    rel="noreferrer"
                    className="text-slate-400 hover:text-slate-600 transition-colors"
                  >
                    <ExternalLink className="w-4 h-4" />
                  </a>
                )}
              </div>
            }
            subtitle={project.description}
            onEdit={() => handleOpenEdit(project)}
            onDelete={() => handleOpenDelete(project)}
          />
        ))}
      </div>

      <TabSaveActions
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        title={editingId ? "Edit Project" : "Add Project"}
        confirmLabel="Save"
        isLoading={isSaving}
        loadingLabel="Saving..."
        onConfirm={handleSave}
      >
        <div className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">Project Title *</label>
            <input
              type="text"
              name="title"
              value={formData.title}
              onChange={handleChange}
              placeholder="e.g. E-Commerce Platform"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">Description</label>
            <textarea
              name="description"
              rows={3}
              value={formData.description}
              onChange={handleChange}
              placeholder="Brief description of the project..."
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all resize-none"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">Project Link / URL</label>
            <input
              type="text"
              name="link"
              value={formData.link}
              onChange={handleChange}
              placeholder="e.g. https://github.com/..."
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>
        </div>
      </TabSaveActions>

      <TabSaveActions
        isOpen={isDeleteOpen}
        onClose={() => setIsDeleteOpen(false)}
        description={
          itemToDelete
            ? `Are you sure you want to delete ${itemToDelete.title}?`
            : "Are you sure you want to delete this project?"
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