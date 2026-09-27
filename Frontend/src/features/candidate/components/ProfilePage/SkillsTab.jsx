import React, { useState } from "react";
import { Plus } from "lucide-react";
import ItemCard from "./shared/ItemCard";
import TabSaveActions from "./TabSaveActions";

const DEFAULT_SKILLS = [
  {
    id: 1,
    name: "React.js",
    isExtracted: true,
    confidence: 96,
    source: "Vercel Partner",
  },
  {
    id: 2,
    name: "Tailwind CSS",
    isExtracted: false,
  },
];

export default function SkillsTab({ initialSkills = DEFAULT_SKILLS }) {
  const [skills, setSkills] = useState(initialSkills);

  const [isAddOpen, setIsAddOpen] = useState(false);
  const [isAdding, setIsAdding] = useState(false);
  const [newSkill, setNewSkill] = useState({
    name: "",
    confidence: "",
    source: "",
  });

  const [isDeleteOpen, setIsDeleteOpen] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [skillToDelete, setSkillToDelete] = useState(null);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewSkill((prev) => ({ ...prev, [name]: value }));
  };

  const handleConfirmAdd = () => {
    if (!newSkill.name.trim()) return;

    setIsAdding(true);
    setTimeout(() => {
      const addedItem = {
        id: Date.now(),
        name: newSkill.name,
        isExtracted: false,
        confidence: newSkill.confidence ? Number(newSkill.confidence) : undefined,
        source: newSkill.source || undefined,
      };

      setSkills((prev) => [...prev, addedItem]);
      setIsAdding(false);
      setIsAddOpen(false);
      setNewSkill({ name: "", confidence: "", source: "" });
    }, 1000);
  };

  const handleOpenDelete = (skill) => {
    setSkillToDelete(skill);
    setIsDeleteOpen(true);
  };

  const handleConfirmDelete = () => {
    if (!skillToDelete) return;

    setIsDeleting(true);
    setTimeout(() => {
      setSkills((prev) => prev.filter((item) => item.id !== skillToDelete.id));
      setIsDeleting(false);
      setIsDeleteOpen(false);
      setSkillToDelete(null);
    }, 1000);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-bold text-slate-900 tracking-tight">
          Skills & AI Evaluation
        </h3>
        <button
          onClick={() => setIsAddOpen(true)}
          className="inline-flex items-center gap-2 bg-[#1E325C] hover:bg-[#162545] text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-sm active:scale-95 cursor-pointer"
        >
          <Plus className="w-4 h-4" />
          <span>Add Skill</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {skills.map((skill) => (
          <ItemCard
            key={skill.id}
            title={
              <div className="flex items-center gap-2">
                <span className="font-bold text-slate-900">{skill.name}</span>
                <span
                  className={`text-xs px-2 py-0.5 rounded-md font-medium ${
                    skill.isExtracted
                      ? "bg-slate-200/60 text-slate-700"
                      : "bg-orange-100 text-orange-700"
                  }`}
                >
                  {skill.isExtracted ? "Extracted from CV" : "Manually Added"}
                </span>
              </div>
            }
            subtitle={
              skill.isExtracted
                ? `Confidence: ${skill.confidence}% • Found in experience (${skill.source})`
                : "Added manually by user"
            }
            onDelete={() => handleOpenDelete(skill)}
            showEdit={false}
          />
        ))}
      </div>

      <TabSaveActions
        isOpen={isAddOpen}
        onClose={() => setIsAddOpen(false)}
        title="Add Skill"
        confirmLabel="Save"
        isLoading={isAdding}
        loadingLabel="Saving..."
        onConfirm={handleConfirmAdd}
      >
        <div className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Skill Name *
            </label>
            <input
              type="text"
              name="name"
              value={newSkill.name}
              onChange={handleInputChange}
              placeholder="e.g. React.js, Node.js"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-gray-700 block">
              Confidence Level (%)
            </label>
            <input
              type="number"
              name="confidence"
              value={newSkill.confidence}
              onChange={handleInputChange}
              placeholder="e.g. 90"
              className="w-full px-4 py-3 bg-[#FAF8F5] border border-gray-200/80 rounded-2xl text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#1E325C]/20 transition-all"
            />
          </div>
        </div>
      </TabSaveActions>

    
      <TabSaveActions
        isOpen={isDeleteOpen}
        onClose={() => setIsDeleteOpen(false)}
        description={
          skillToDelete
            ? `Are you sure you want to delete ${skillToDelete.name}?`
            : "Are you sure you want to delete this skill?"
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