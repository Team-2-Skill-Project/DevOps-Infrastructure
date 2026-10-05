import { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import {
  Sparkles,
  CheckCircle2,
  Briefcase,
  GraduationCap,
  Languages,
  User,
  Layers,
  FolderGit2,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

import CvProfileCard from "./CvProfileCard";
import CvExperiencesCard from "./CvExperiencesCard";
import CvEducationCard from "./CvEducationCard";
import CvSkillsCard from "./CvSkillsCard";
import CvLanguagesCard from "./CvLanguagesCard";

const ease = [0.16, 1, 0.3, 1];

export default function CvExtractionReview({
  extractionData,
  skills: legacySkills,
  onConfirmSkill,
  onConfirmExperience,
  onConfirmEducation,
  onConfirmLanguage,
  onConfirmProfile,
  onConfirmAll,
}) {
  const [activeTab, setActiveTab] = useState("all");

  // Normalize data support (both extractionData object and legacy skills prop)
  const skills = extractionData?.candidate_skills || legacySkills || [];
  const experiences = extractionData?.experiences || [];
  const educations = extractionData?.educations || [];
  const languages = extractionData?.languages || [];
  const user = extractionData?.user || null;
  const profile = extractionData?.candidate_profile || null;
  const profileConfirmed = extractionData?.profileConfirmed || false;

  // Calculate confirmation stats
  const totalItems =
    (user || profile ? 1 : 0) +
    experiences.length +
    educations.length +
    skills.length +
    languages.length;

  const confirmedCount =
    (profileConfirmed ? 1 : 0) +
    experiences.filter((e) => e.confirmed).length +
    educations.filter((e) => e.confirmed).length +
    skills.filter((s) => s.confirmed).length +
    languages.filter((l) => l.confirmed).length;

  const allConfirmed = totalItems > 0 && confirmedCount === totalItems;

  const tabs = [
    {
      id: "all",
      label: "All Extracted Data",
      icon: Layers,
      count: totalItems,
    },
    {
      id: "skills",
      label: "Skills",
      icon: Sparkles,
      count: skills.length,
    },
    {
      id: "experience",
      label: "Experience",
      icon: Briefcase,
      count: experiences.length,
    },
    {
      id: "education",
      label: "Education",
      icon: GraduationCap,
      count: educations.length,
    },
    {
      id: "profile",
      label: "Profile & Bio",
      icon: User,
      count: user || profile ? 1 : 0,
    },
    {
      id: "languages",
      label: "Languages",
      icon: Languages,
      count: languages.length,
    },
  ];

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 0.16, ease }}
      className="space-y-6"
    >
      {/* Header and Quick Stats */}
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 className="flex items-center gap-2 text-lg font-bold text-primary">
            <Sparkles className="h-5 w-5 text-accent" />
            AI Extraction Review
          </h3>
          <p className="text-xs text-muted">
            Verify and confirm your parsed professional background before updating your profile
          </p>
        </div>

        {/* Verification Progress Badge */}
        <div className="flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-1.5 text-xs">
          <span className="text-muted">Verification:</span>
          <span className="font-bold text-primary">
            {confirmedCount} of {totalItems} confirmed
          </span>
          {allConfirmed && (
            <CheckCircle2 className="h-4 w-4 text-success" />
          )}
        </div>
      </div>

      {/* Navigation Filter Tabs */}
      <div className="flex items-center gap-1.5 overflow-x-auto rounded-xl border border-border bg-background/60 p-1 scrollbar-none">
        {tabs.map((tab) => {
          const Icon = tab.icon;
          const isActive = activeTab === tab.id;
          return (
            <button
              key={tab.id}
              type="button"
              onClick={() => setActiveTab(tab.id)}
              className={`flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold whitespace-nowrap transition-all cursor-pointer ${
                isActive
                  ? "bg-surface text-primary shadow-xs border border-border/80"
                  : "text-muted hover:text-ink hover:bg-surface/50"
              }`}
            >
              <Icon className={`h-3.5 w-3.5 ${isActive ? "text-primary" : "text-muted"}`} />
              <span>{tab.label}</span>
              {typeof tab.count === "number" && (
                <span
                  className={`ml-1 rounded-full px-1.5 py-0.2 text-[10px] ${
                    isActive
                      ? "bg-primary text-primary-foreground font-bold"
                      : "bg-muted/15 text-muted font-medium"
                  }`}
                >
                  {tab.count}
                </span>
              )}
            </button>
          );
        })}
      </div>

      {/* Main Extracted Content Area */}
      <AnimatePresence mode="wait">
        <motion.div
          key={activeTab}
          initial={{ opacity: 0, y: 6 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.25, ease }}
          className="space-y-6"
        >
          {/* Profile Section */}
          {(activeTab === "all" || activeTab === "profile") && (
            <CvProfileCard
              user={user}
              profile={profile}
              confirmed={profileConfirmed}
              onConfirm={onConfirmProfile}
            />
          )}

          {/* Skills Section */}
          {(activeTab === "all" || activeTab === "skills") && (
            <CvSkillsCard
              skills={skills}
              onConfirmSkill={onConfirmSkill}
            />
          )}

          {/* Work Experience Section */}
          {(activeTab === "all" || activeTab === "experience") && (
            <CvExperiencesCard
              experiences={experiences}
              onConfirmExperience={onConfirmExperience}
            />
          )}

          {/* Education Section */}
          {(activeTab === "all" || activeTab === "education") && (
            <CvEducationCard
              educations={educations}
              onConfirmEducation={onConfirmEducation}
            />
          )}

          {/* Languages Section */}
          {(activeTab === "all" || activeTab === "languages") && (
            <CvLanguagesCard
              languages={languages}
              onConfirmLanguage={onConfirmLanguage}
            />
          )}
        </motion.div>
      </AnimatePresence>

      {/* Action Footer */}
      <div className="flex flex-col gap-3 rounded-2xl border border-border bg-surface p-4 sm:flex-row sm:items-center sm:justify-between shadow-xs">
        <div className="text-xs text-muted">
          <p className="font-semibold text-ink">Ready to sync with your candidate profile?</p>
          <p>Confirming extracted data will update your MatchIn profile information automatically.</p>
        </div>

        <div className="flex items-center gap-3">
          <Button
            type="button"
            onClick={onConfirmAll}
            disabled={allConfirmed || totalItems === 0}
            className="flex items-center gap-2 rounded-xl bg-success px-6 py-2.5 text-xs font-bold text-success-foreground hover:bg-success/90 disabled:opacity-50"
          >
            <CheckCircle2 className="h-4 w-4" />
            {allConfirmed ? "All Data Confirmed" : "Confirm All Extracted Data"}
          </Button>
        </div>
      </div>
    </motion.div>
  );
}
