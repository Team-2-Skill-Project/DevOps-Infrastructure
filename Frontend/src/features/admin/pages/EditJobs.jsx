import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { RotateCcw, CheckCircle2, ArrowLeft } from 'lucide-react';
import { useTranslation } from "react-i18next";

// استدعاء المكونات الفرعية
import UnsavedChangesBanner from '../components/editJobs/UnsavedChangesBanner';
import JobInformationSection from '../components/editJobs/JobInformationSection';
import DescriptionSection from '../components/editJobs/DescriptionSection';
import ResponsibilitiesSection from '../components/editJobs/ResponsibilitiesSection';
import SkillsSection from '../components/editJobs/SkillsSection';
import LocationWorkSection from '../components/editJobs/LocationWorkSection';
import StatusSection from '../components/editJobs/StatusSection';
import SkeletonLoader from '../components/editJobs/SkeletonLoader';

const initialData = {
  jobTitle: 'Senior Frontend Engineer',
  company: 'BrightPath Inc.',
  source: 'Internal',
  description: 'We are looking for a Senior Frontend Engineer to join our product team and help build delightful, performant user experiences across our platform. You will work closely with design and backend teams to ship features that matter.',
  responsibilities: '• Own and evolve core frontend modules using React & TypeScript\n• Collaborate with design to implement accessible, responsive UIs\n• Write clean, tested code and participate in code reviews\n• Mentor junior engineers and contribute to technical decisions\n• Improve performance and monitoring of critical user flows',
  requiredSkills: ['React', 'TypeScript', 'CSS / Tailwind', 'Git'],
  preferredSkills: ['Next.js', 'GraphQL', 'Framer Motion'],
  location: 'Cairo, Egypt',
  experienceLevel: 'Senior',
  workMode: 'Hybrid',
  jobType: 'Full-time',
  status: 'Open'
};

export default function EditJob() {
  const { t } = useTranslation("common");
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [formData, setFormData] = useState(initialData);
  const [savedData, setSavedData] = useState(initialData);
  const [isDirty, setIsDirty] = useState(false);

  // محاكاة تحميل البيانات
  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1200);
    return () => clearTimeout(timer);
  }, []);

  // متابعة التغييرات غير المحفوظة
  useEffect(() => {
    const hasChanged = JSON.stringify(formData) !== JSON.stringify(savedData);
    setIsDirty(hasChanged);
  }, [formData, savedData]);

  const handleChange = (field, value) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handleReset = () => {
    setFormData(savedData);
  };

  const handleSave = () => {
    setIsSaving(true);
    setTimeout(() => {
      setSavedData(formData);
      setIsSaving(false);
      setIsDirty(false);
    }, 1000);
  };

  if (isLoading) {
    return <SkeletonLoader />;
  }

  return (
    <div className="min-h-screen bg-[#FBF9F5] p-6 md:p-8 text-[#18181B] font-sans">
      <div className="max-w-5xl mx-auto space-y-6">
        {/* Header & Breadcrumb */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#E8E6E1] pb-5">
          <div>
            <div className="flex items-center gap-1.5 text-xs text-[#71717A] mb-2">
              <span>{t("ui.editJob.jobs")}</span>
              <span>/</span>
              <span>JOB-2841</span>
              <span>/</span>
              <span className="text-[#09090B] font-medium">{t("ui.editJob.edit")}</span>
            </div>
            <h1 className="text-2xl md:text-3xl font-bold tracking-tight text-[#09090B]">
              {t("ui.editJob.title")}
            </h1>
            <p className="text-xs text-[#71717A] mt-1.5">
              ID: JOB-2841 <span className="mx-1">•</span> {t("ui.editJob.updated", { time: "3 hours" })} <span className="mx-1">•</span> {t("ui.editJob.by")} BrightPath Inc.
            </p>
          </div>

          {/* Action Buttons Top */}
          <div className="flex items-center gap-2.5">
            <button
              type="button"
              onClick={handleReset}
              className="flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-[#52525B] hover:text-[#09090B] hover:bg-white/60 rounded-lg transition cursor-pointer"
            >
              <ArrowLeft size={14} />
              {t("ui.editJob.cancel")}
            </button>

            <button
              type="button"
              onClick={handleReset}
              disabled={!isDirty || isSaving}
              className="flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-[#09090B] bg-white border border-[#E4E4E7] rounded-lg hover:bg-[#F4F4F5] disabled:opacity-40 transition shadow-xs cursor-pointer"
            >
              <RotateCcw size={14} />
              {t("ui.editJob.reset")}
            </button>

            <button
              type="button"
              onClick={handleSave}
              disabled={!isDirty || isSaving}
              className="flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white bg-[#1D2A44] rounded-lg hover:bg-[#162034] disabled:opacity-40 transition shadow-xs cursor-pointer"
            >
              <CheckCircle2 size={14} />
              {isSaving ? t("ui.editJob.saving") : t("ui.editJob.saveChanges")}
            </button>
          </div>
        </div>

        {/* Unsaved Changes Banner */}
        <AnimatePresence>
          {isDirty && (
            <motion.div
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              transition={{ duration: 0.2 }}
            >
              <UnsavedChangesBanner onReset={handleReset} onSave={handleSave} isSaving={isSaving} />
            </motion.div>
          )}
        </AnimatePresence>

        {/* Sections */}
        <div className="space-y-6">
          <JobInformationSection formData={formData} onChange={handleChange} />
          <DescriptionSection formData={formData} onChange={handleChange} />
          <ResponsibilitiesSection formData={formData} onChange={handleChange} />
          <SkillsSection formData={formData} onChange={handleChange} />
          <LocationWorkSection formData={formData} onChange={handleChange} />
          <StatusSection formData={formData} onChange={handleChange} />
        </div>

        {/* Bottom Actions */}
        <div className="flex items-center justify-between pt-6 border-t border-[#E8E6E1]">
          <button
            type="button"
            onClick={handleReset}
            disabled={!isDirty || isSaving}
            className="flex items-center gap-1.5 text-xs font-medium text-[#DC2626] hover:text-[#B91C1C] disabled:opacity-30 transition cursor-pointer"
          >
            <RotateCcw size={14} />
            {t("ui.editJob.resetChanges")}
          </button>

          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={handleReset}
              className="px-4 py-2 text-xs font-medium text-[#09090B] bg-white border border-[#E4E4E7] rounded-lg hover:bg-[#F4F4F5] transition shadow-xs cursor-pointer"
            >
              {t("ui.editJob.cancel")}
            </button>
            <button
              type="button"
              onClick={handleSave}
              disabled={!isDirty || isSaving}
              className="flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white bg-[#1D2A44] rounded-lg hover:bg-[#162034] disabled:opacity-40 transition shadow-xs cursor-pointer"
            >
              <CheckCircle2 size={14} />
              {isSaving ? t("ui.editJob.saving") : t("ui.editJob.saveChanges")}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
}