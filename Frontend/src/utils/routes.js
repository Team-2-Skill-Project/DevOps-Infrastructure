import { useParams } from "react-router-dom";

export const DEFAULT_LANGUAGE = "en";

export function localizedPath(path, language = DEFAULT_LANGUAGE) {
  if (!path || path === "#") return path;
  if (!path.startsWith("/")) return path;
  if (path === "/") return `/${language}`;

  const normalizedPath = path.replace(/^\/+/, "");
  return `/${language}/${normalizedPath}`.replace(/\/{2,}/g, "/");
}

export function useLocalizedPath() {
  const { lang = DEFAULT_LANGUAGE } = useParams();
  return (path) => localizedPath(path, lang);
}
