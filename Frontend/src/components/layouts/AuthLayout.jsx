import AuthCard from "@/features/auth/shared/AuthCard";
import AuthSidePanel from "@/features/auth/shared/AuthSidePanel";
import LanguageSwitcher from "@/components/shared/LanguageSwitcher";

export default function AuthLayout({
  sidePanel,
  topRight,
  aboveCard,
  footer,
  additional,
  cardClassName,
  children,
}) {
  return (
    <div className="grid h-screen grid-cols-1 bg-background lg:grid-cols-[1.05fr_1fr]">
      <AuthSidePanel {...sidePanel} />

      <main className="flex h-screen flex-col overflow-y-auto scrollbar-none [&::-webkit-scrollbar]:hidden">
        <div className="mx-auto flex w-full max-w-2xl shrink-0 items-center justify-end px-12 pb-1 pt-[1.1rem]">
          {topRight || <LanguageSwitcher />}
        </div>

        <div className="mx-auto w-full max-w-2xl shrink-0 px-8.75 pb-5">
          {additional}
        </div>

        {aboveCard && (
          <div className="mx-auto w-full max-w-2xl shrink-0 px-12 pb-[0.9rem]">
            {aboveCard}
          </div>
        )}

        <div className="mx-auto flex min-h-0 w-full max-w-2xl flex-1 flex-col px-12 pb-5">
          <AuthCard
            children={children}
            className={cardClassName && cardClassName}
          />
        </div>

        {footer && (
          <p className="pb-6 text-center text-[13px] text-[#44474E]">
            {footer}
          </p>
        )}
      </main>
    </div>
  );
}
