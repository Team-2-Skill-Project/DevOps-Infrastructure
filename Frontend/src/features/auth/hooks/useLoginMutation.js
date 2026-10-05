import { useDispatch } from "react-redux";
import { loginRequest } from "../api/auth.api";
import { setCredentials } from "../store/auth.slice";
import { tokenStorage } from "@/lib/token";
import { useApiMutation } from "./useApiMutation";

// The login response already includes the user object directly:
// { access_token, token_type, expires_in, user: { id, name, email, role, ... } }
// So we don't need a second /me request here — that stays in useInitializeAuth for page reloads.
export const useLoginMutation = ({ onAuthenticated } = {}) => {
  const dispatch = useDispatch();

  return useApiMutation({
    mutationFn: loginRequest,
    onSuccess: (data) => {
      tokenStorage.set(data.access_token);
      const user = data.user;
      dispatch(setCredentials({ user, token: data.access_token }));
      onAuthenticated?.(user);
    },
  });
};
