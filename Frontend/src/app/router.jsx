import MainLayout from "@/components/layouts/MainLayout";
import DashboardLayout from "@/components/layouts/DashboardLayout/DashboardLayout";
import HomePage from "@/features/public/pages/HomePage";
import NotFoundPage from "@/features/public/pages/NotFoundPage";

import { createBrowserRouter } from "react-router-dom";
import { auth } from "./routes/auth.routes";
import {
  candidateDashboardRoutes,
  adminDashboardRoutes,
} from "./routes/guards/dashboard.routes";
import LanguageLayout from "./routes/LanguageLayout";
import RootRedirect from "./routes/RootRedirect";
import { RequireAuth } from "./routes/guards/RequireAuth";
import { RequireRole } from "./routes/guards/RequireRole";

export const router = createBrowserRouter([
  // Root redirect
  {
    path: "/",
    element: <RootRedirect />,
  },

  // Language routes
  {
    path: "/:lang",
    element: <LanguageLayout />,
    children: [
      // Guest routes
      {
        element: <MainLayout />,
        children: [
          {
            index: true,
            element: <HomePage />,
          },
          {
            path: "auth",
            children: [...auth],
          },
        ],
      },

      // Dashboard routes — must be logged in (RequireAuth), then routed to
      // the matching role's tree (RequireRole). DashboardLayout no longer
      // takes hardcoded userName/userRole props — it reads the real user
      // from Redux internally, same as every other authenticated screen.
      {
        path: "dashboard",
        element: <RequireAuth />,
        children: [
          {
            element: <DashboardLayout />,
            children: [
              {
                element: <RequireRole allow={["candidate"]} />,
                children: candidateDashboardRoutes,
              },
              {
                element: <RequireRole allow={["admin"]} />,
                children: adminDashboardRoutes,
              },
            ],
          },
        ],
      },
    ],
  },

  // Not Found
  {
    path: "*",
    element: <NotFoundPage />,
  },
]);
