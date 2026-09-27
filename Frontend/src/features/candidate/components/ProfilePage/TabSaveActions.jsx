import React from "react";
import { X, Loader2 } from "lucide-react";

export default function TabSaveActions({
  isOpen,
  onClose,
  title,
  description,
  children,
  onConfirm,
  confirmLabel = "Save",
  cancelLabel = "Cancel",
  isLoading = false,
  loadingLabel = "Saving...",
  variant = "primary",
}) {
  if (!isOpen) return null;

  const buttonVariantStyles = {
    primary: "bg-[#1E325C] hover:bg-[#162545] text-white",
    danger: "bg-[#C5221F] hover:bg-[#A81C19] text-white",
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
      <div className="w-full max-w-lg bg-white rounded-[28px] p-6 md:p-8 shadow-2xl relative transition-all animate-in fade-in zoom-in-95 duration-200">
        {title && (
          <button
            onClick={onClose}
            type="button"
            className="absolute top-6 inset-e-6 text-gray-400 hover:text-gray-600 transition-colors p-1 cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        )}

        {title && (
          <div className="pb-4 border-b border-gray-100 mb-6">
            <h2 className="text-xl font-bold text-[#1E325C] pe-8 tracking-tight">
              {title}
            </h2>
          </div>
        )}

        {description && (
          <div className="text-center space-y-3 my-4">
            <h2 className="text-2xl font-bold text-gray-900 tracking-tight">
              Confirm Delete
            </h2>
            <p className="text-sm font-medium text-gray-500 max-w-xs mx-auto leading-relaxed">
              {description}
            </p>
          </div>
        )}

        {children && <div className="space-y-4 mb-6">{children}</div>}

        <div
          className={`flex items-center gap-3 pt-2 ${
            description ? "justify-center" : "justify-end"
          }`}
        >
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-6 py-2.5 rounded-2xl border border-gray-200 bg-white text-xs font-bold text-gray-800 hover:bg-gray-50 transition-all cursor-pointer disabled:opacity-50"
          >
            {cancelLabel}
          </button>

          <button
            type="button"
            onClick={onConfirm}
            disabled={isLoading}
            className={`inline-flex items-center justify-center gap-2.5 px-6 py-2.5 rounded-2xl text-xs font-bold transition-all min-w-[110px] cursor-pointer disabled:opacity-95 ${buttonVariantStyles[variant]}`}
          >
            {isLoading ? (
              <>
                <Loader2 className="w-4 h-4 animate-spin text-white/80" />
                <span>{loadingLabel}</span>
              </>
            ) : (
              <span>{confirmLabel}</span>
            )}
          </button>
        </div>
      </div>
    </div>
  );
}
