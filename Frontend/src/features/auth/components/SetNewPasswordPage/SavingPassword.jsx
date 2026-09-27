import { Loader2 } from "lucide-react";
import ResetStateView from "./ResetStateView";
import { useTranslation } from "react-i18next";

export default function SavingPassword() {
  const { t } = useTranslation("common");
  return (
    <ResetStateView
      tone="blue"
      icon={<Loader2 className="h-7 w-7 animate-spin" />}
      title={t("auth.reset.saving")}
      desc={t("auth.reset.savingDescription")}
    />
  );
}
