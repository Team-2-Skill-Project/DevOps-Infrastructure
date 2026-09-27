import React, { useState } from "react";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import ProfileHeader from "../components/ProfilePage/ProfileHeader";

// Tabs Imports
import PersonalInfoTab from "../components/ProfilePage/PersonalInfoTab";
import SkillsTab from "../components/ProfilePage/SkillsTab";
import ExperienceTab from "../components/ProfilePage/ExperienceTab";
import EducationTab from "../components/ProfilePage/EducationTab";
import ProjectsTab from "../components/ProfilePage/ProjectsTab";
import CareerPreferencesTab from "../components/ProfilePage/PreferencesTab";

const TABS = [
  { id: "personal-info", label: "Personal Info", Component: PersonalInfoTab },
  { id: "skills", label: "Skills", Component: SkillsTab },
  { id: "experience", label: "Experience", Component: ExperienceTab },
  { id: "education", label: "Education", Component: EducationTab },
  { id: "projects", label: "Projects", Component: ProjectsTab },
  {
    id: "career-preferences",
    label: "Career Preferences",
    Component: CareerPreferencesTab,
  },
];

const triggerClass = [
  "px-5 py-3.5 text-sm font-medium whitespace-nowrap cursor-pointer",
  "rounded-none border-0 border-b-2 border-transparent -mb-[1px]",
  "bg-transparent shadow-none text-gray-500 transition-all duration-200",
  "hover:text-gray-800 hover:border-gray-300",
  "data-[state=active]:bg-transparent data-[state=active]:shadow-none",
  "data-[state=active]:border-slate-800 data-[state=active]:text-slate-900 data-[state=active]:font-bold",
  "focus-visible:outline-none focus-visible:ring-0",
].join(" ");

export default function ProfilePage() {
  const [activeTab, setActiveTab] = useState("personal-info");

  return (
    <div className="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
      {/* Reusable Profile Header */}
      <ProfileHeader />

      {/* Tabs */}
      <div className="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <Tabs
          value={activeTab}
          onValueChange={setActiveTab}
          className="w-full gap-0"
        >
          <TabsList className="h-auto w-full justify-start gap-0 p-0 px-6 pt-2 bg-transparent rounded-none border-b border-gray-200 overflow-x-auto scrollbar-none">
            {TABS.map((tab) => (
              <TabsTrigger key={tab.id} value={tab.id} className={triggerClass}>
                {tab.label}
              </TabsTrigger>
            ))}
          </TabsList>

          {TABS.map(({ id, Component }) => (
            <TabsContent key={id} value={id} className="p-6 mt-0">
              <Component />
            </TabsContent>
          ))}
        </Tabs>
      </div>
    </div>
  );
}
