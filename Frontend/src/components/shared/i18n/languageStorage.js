const LAST_ROUTE_KEY = "skillmatch_last_route";

export const getLastRoute = () => {
  const storedValue = localStorage.getItem(LAST_ROUTE_KEY);

  if (storedValue === "en" || storedValue === "ar") return storedValue;

  const legacyLanguage = storedValue?.match(/^\/(en|ar)(?:\/|$)/)?.[1];
  if (legacyLanguage) {
    localStorage.setItem(LAST_ROUTE_KEY, legacyLanguage);
    return legacyLanguage;
  }

  return null;
};

export const saveLastRoute = (language) => {
  if (language !== "en" && language !== "ar") return;

  localStorage.setItem(LAST_ROUTE_KEY, language);
};
