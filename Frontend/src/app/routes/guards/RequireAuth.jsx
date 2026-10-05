// src/routes/guards/RequireAuth.jsx
import { useSelector } from "react-redux";
import { Navigate, Outlet, useLocation, useParams } from "react-router-dom";
import { selectAuthStatus } from "@/features/auth/store/auth.selectors";

export function RequireAuth() {
  const status = useSelector(selectAuthStatus);
  const location = useLocation();
  const { lang } = useParams();

  // Still verifying a cookie-stored token — render nothing (or a spinner)
  // rather than redirecting too early.
  if (status === "idle" || status === "loading") {
    return null;
  }

  if (status !== "authenticated") {
    return (
      <Navigate
        to={`/${lang}/auth/login`}
        state={{ from: location }}
        replace
      />
    );
  }

  return <Outlet />;
}
