import React from "react";
import AuthLayout from "@/components/layouts/AuthLayout";
import { BriefcaseBusiness } from "lucide-react";
import { useTranslation } from "react-i18next";
import LoginFooter from "@/features/auth/components/LoginPage/LoginFooter";
import LoginForm from "@/features/auth/components/LoginPage/LoginForm";

export default function LoginPage() {
  const { t } = useTranslation("common");

  const handleLogin = (data) => {
    console.log("Login submitted:", data);
  };

  return (
    <AuthLayout
      sidePanel={{
        imageSrc:
          "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80",
        badgeText: t("auth.side.badge"),
        badgeIcon: <BriefcaseBusiness width={13} height={13} />,
        title: t("auth.side.title"),
        description: t("auth.side.description"),
      }}
      footer={<LoginFooter />}
    >
      <LoginForm onSubmit={handleLogin} />
    </AuthLayout>
  );
}
