// src/routes/guards/RequireRole.jsx
import { useSelector } from "react-redux";
import { Navigate, Outlet, useParams } from "react-router-dom";
import { selectUserRole } from "@/features/auth/store/auth.selectors";

/**
 * Usage: <RequireRole allow={["admin"]} /> as a parent route with the
 * protected routes as its children.
 */
export function RequireRole({ allow }) {
  const role = useSelector(selectUserRole);
  const { lang } = useParams();

  if (!role || !allow.includes(role)) {
    return <Navigate to={`/${lang}/dashboard`} replace />;
  }

  return <Outlet />;
}
