// src/features/auth/hooks/useInitializeAuth.js
import { useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { meRequest } from "../api/auth.api";
import { setCredentials, clearCredentials } from "../store/auth.slice";
import { selectAuthStatus } from "../store/auth.selectors";
import { tokenStorage } from "@/lib/token";

/**
 * Call once at the app root (e.g. App.jsx). If a token cookie survived a
 * refresh, status starts as "loading" (see auth.slice initialState) — this
 * verifies it against /auth/me and resolves status to "authenticated" or
 * "unauthenticated". Route guards read that status, not the raw cookie.
 */
export const useInitializeAuth = () => {
  const dispatch = useDispatch();
  const status = useSelector(selectAuthStatus);

  useEffect(() => {
    if (status !== "loading") return;

    meRequest()
      .then((user) => dispatch(setCredentials({ user, token: tokenStorage.get() })))
      .catch(() => {
        tokenStorage.clear();
        dispatch(clearCredentials());
      });
  }, [status, dispatch]);
};
