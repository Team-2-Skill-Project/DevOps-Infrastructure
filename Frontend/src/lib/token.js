import Cookies from "js-cookie";

const TOKEN_KEY = "access_token";

export const tokenStorage = {
  get: () => Cookies.get(TOKEN_KEY) ?? null,
  set: (token) =>
    Cookies.set(TOKEN_KEY, token, { expires: 7, sameSite: "strict" }),
  clear: () => Cookies.remove(TOKEN_KEY),
};
