import React from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";

const tabs = [
  { label: "Personal Info", path: "personal-info" },
  { label: "Skills", path: "skills" },
  { label: "Experience", path: "experience" },
  { label: "Education", path: "education" },
  { label: "Projects", path: "projects" },
  { label: "Career Preferences", path: "career-preferences" },
];

const triggerClass = [
  "relative pb-4  px-1 text-sm font-semibold whitespace-nowrap cursor-pointer",
  "rounded-none border-0 bg-transparent shadow-none",
  "text-slate-500 transition-all duration-200 hover:text-slate-800",
  "data-[state=active]:bg-transparent data-[state=active]:shadow-none",
  "data-[state=active]:text-[#1E3A8A]",
  "after:absolute after:bottom-0 after:inset-s-0 after:inset-e-0 after:h-0.5 after:rounded-t-full",
  "after:bg-[#1E3A8A] after:opacity-0 data-[state=active]:after:opacity-100",
  "focus-visible:outline-none focus-visible:ring-0",
].join(" ");

export default function ProfileTabsNav() {
  const navigate = useNavigate();
  const { pathname } = useLocation();

  // نحدد التاب النشط من الـ URL
  const active =
    tabs.find((tab) => pathname.includes(tab.path))?.path ?? tabs[0].path;

  return (
    <div className="w-full border-b  border-gray-100 bg-white px-6 pt-2">
      <Tabs value={active} onValueChange={(value) => navigate(value)}>
        <TabsList
          aria-label="Tabs"
          className="h-auto  w-full justify-start gap-8  bg-transparent rounded-none overflow-x-auto scrollbar-none"
        >
          {tabs.map((tab) => (
            <TabsTrigger
              key={tab.path}
              value={tab.path}
              className={triggerClass}
            >
              {tab.label}
            </TabsTrigger>
          ))}
        </TabsList>
      </Tabs>
    </div>
  );
}
