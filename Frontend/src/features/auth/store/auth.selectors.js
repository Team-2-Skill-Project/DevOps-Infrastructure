export const selectCurrentUser = (state) => state.auth.user;
export const selectUserRole = (state) => state.auth.user?.role ?? null;
export const selectAuthStatus = (state) => state.auth.status;
export const selectIsAuthenticated = (state) =>
  state.auth.status === "authenticated";
