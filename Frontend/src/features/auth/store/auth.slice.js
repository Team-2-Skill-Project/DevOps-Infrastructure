// src/features/auth/store/auth.slice.js
import { createSlice } from "@reduxjs/toolkit";
import { tokenStorage } from "@/lib/token";

const initialState = {
  user: null,
  token: tokenStorage.get(),
  // 'idle'          -> not checked yet (shouldn't really be hit, see below)
  // 'loading'       -> token exists in the cookie, verifying it via /auth/me
  // 'authenticated' | 'unauthenticated' -> known result
  status: tokenStorage.get() ? "loading" : "unauthenticated",
};

const authSlice = createSlice({
  name: "auth",
  initialState,
  reducers: {
    setCredentials: (state, action) => {
      state.user = action.payload.user;
      state.token = action.payload.token;
      state.status = "authenticated";
    },
    updateUser: (state, action) => {
      state.user = { ...state.user, ...action.payload };
    },
    // Pure: only clears Redux state. Clearing the cookie is a side effect
    // and belongs at the call site (tokenStorage.clear()), not in a reducer.
    clearCredentials: (state) => {
      state.user = null;
      state.token = null;
      state.status = "unauthenticated";
    },
  },
});

export const { setCredentials, updateUser, clearCredentials } =
  authSlice.actions;
export default authSlice.reducer;
