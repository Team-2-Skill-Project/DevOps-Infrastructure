import { LayoutDashboard, Compass, Bell, FileText, Bot, Bookmark,PersonStanding ,ClipboardList} from "lucide-react";
import AiChatPage from "@/features/candidate/pages/AiChatPage";
import ApplicationDetail from "@/features/candidate/pages/ApplicationDetail";
import ApplicationPage from "@/features/candidate/pages/ApplicationPage";
import CvManagementPage from "@/features/candidate/pages/CvManagementPage";
import JobsDetails from "@/features/candidate/pages/JobsDetails";
import JobsPage from "@/features/candidate/pages/JobsPage";
import NotificationsPage from "@/features/candidate/pages/NotificationsPage";
import OnboardingPage from "@/features/candidate/pages/OnboardingPage";
import Overview from "@/features/candidate/pages/Overview";
import ProfilePage from "@/features/candidate/pages/ProfilePage";
import RoadmapDetailsPage from "@/features/candidate/pages/RoadmapDetailsPage";
import RoadmapPage from "@/features/candidate/pages/RoadmapPage";
import SavedJobs from "@/features/candidate/pages/SavedJobs";


export const dashboard = [
  {
    index: true,
    element: <Overview />,
    handle: { label: "Dashboard", labelKey: "navigation.dashboard", icon: LayoutDashboard, sidebar: true },
  },
  {
    path: "notifications",
    element: <NotificationsPage />,
    handle: { label: "Notifications", icon: Bell, sidebar: false },
  },
  {
    path: "profile",
    element: <ProfilePage />,
    handle: { label: "Profile", icon: PersonStanding, sidebar: false },
  },
  {
    path: "jobs",
    handle: { label: "Explore Jobs", labelKey: "navigation.exploreJobs", icon: Compass, sidebar: true },
    children: [
      { index: true, element: <JobsPage /> },
      { path: ":jobId", element: <JobsDetails /> },
    ],
  },
  {
    path: "saved-jobs",
    element: <SavedJobs />,
    handle: { label: "Saved Jobs", labelKey: "navigation.savedJobs", icon: Bookmark, sidebar: true },
  },
  {
    path: "cv-management",
    element: <CvManagementPage />,
    handle: { label: "Cv Management", labelKey: "navigation.cvManagement", icon: FileText, sidebar: true },
  },
  {
    path: "ai-chat",
    element: <AiChatPage />,
    handle: { label: "AI Chat", labelKey: "navigation.aiChat", icon: Bot, sidebar: true },
  },
  {
    path: "roadmap",
    handle: { label: "Roadmap", labelKey: "navigation.roadmap", icon: Compass, sidebar: true },
    children: [
      { index: true, element: <RoadmapPage /> },
      { path: ":roleId", element: <RoadmapDetailsPage /> },
    ],
  },
  {
    path: "onboarding",
    element: <OnboardingPage />,
  },
  {
    path: "applications",
    handle: {
      label: "Application Tracker",
      labelKey: "navigation.applicationTracker",
      icon: ClipboardList,
      sidebar: true,
    },
    children: [
      {index: true, element: <ApplicationPage />},
      {path: ":applicationId", element: <ApplicationDetail />}
    ]
  }
]
   
