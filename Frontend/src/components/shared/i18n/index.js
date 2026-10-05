import i18n from "i18next";
import { initReactI18next } from "react-i18next";

import enCommon from "./locales/en/common.json";
import arCommon from "./locales/ar/common.json";
import enDashboard from "./locales/en/dashboard.json";
import arDashboard from "./locales/ar/dashboard.json";
import enValidation from "./locales/en/validation.json";
import arValidation from "./locales/ar/validation.json";

i18n.use(initReactI18next).init({
  resources: {
    en: {
      common: enCommon,
      dashboard: enDashboard,
      validation: enValidation,
    },
    ar: {
      common: arCommon,
      dashboard: arDashboard,
      validation: arValidation,
    },
  },

  fallbackLng: "en",

  defaultNS: "common",

  interpolation: {
    escapeValue: false,
  },
});

export default i18n;
