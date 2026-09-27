
export default function Status({
  icon,
  title,
  subtitle,
  iconClassName = "",
  primaryButton,
  secondaryButton,

  children,
}) {
  return (

      <div className="p-8 text-center">
        {icon && (
          <div
            className={`mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full ${iconClassName}`}
          >
            {icon}
          </div>
        )}
        <h2 className="mb-2 text-lg font-bold text-primary">{title}</h2>
        <p className="mb-5 text-xs text-muted">{subtitle}</p>
        {children}
        <div className="flex gap-3">
          {secondaryButton && (
            <button
              type="button"
              onClick={secondaryButton.onClick}
              disabled={secondaryButton.disabled}
              className="h-10 flex-1 rounded-xl border border-border text-xs font-bold hover:bg-background disabled:cursor-not-allowed disabled:opacity-50"
            >
              {secondaryButton.label}
            </button>
          )}
          {primaryButton && (
            <button
              type="button"
              onClick={primaryButton.onClick}
              disabled={primaryButton.disabled}
              className="h-10 flex-1 rounded-xl bg-primary text-xs font-bold text-white hover:bg-[#052045] disabled:cursor-not-allowed disabled:opacity-50"
            >
              {primaryButton.label}
            </button>
          )}
        </div>
      </div>

  );
}
