import { Compass, Send, Bookmark, Route, MessageSquare } from "lucide-react";

export const DASHBOARD_QUICK_ACTIONS = [
  { key: "browseJobs", label: "Browse Jobs", to: "/dashboard/jobs", icon: Compass, variant: "primary" },
  {
    key: "viewApplications", label: "View Applications",
    to: "/dashboard/applications",
    icon: Send,
    variant: "secondary",
  },
  {
    key: "savedJobs", label: "Saved Jobs",
    to: "/dashboard/saved-jobs",
    icon: Bookmark,
    variant: "secondary",
  },
  {
    key: "openRoadmap", label: "Open Roadmap",
    to: "/dashboard/roadmap",
    icon: Route,
    variant: "secondary",
  },
  {
    key: "askAiMentor", label: "Ask AI Mentor",
    to: "/dashboard/ai-chat",
    icon: MessageSquare,
    variant: "secondary",
  },
];
