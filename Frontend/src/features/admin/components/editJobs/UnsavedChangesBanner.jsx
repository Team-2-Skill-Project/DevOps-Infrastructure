import { AlertCircle } from "lucide-react";

export default function UnsavedChangesBanner({ onReset, onSave, isSaving }) {
  return (
    <div className="flex items-center justify-between rounded-2xl bg-[#FDF6EA] border border-[#F6E8D3] px-5 py-3.5 text-[#B87322]">
      <div className="flex items-center gap-3">
        <AlertCircle className="h-5 w-5 text-[#B87322] shrink-0" />
        <span className="text-sm font-semibold">
          You have unsaved changes
        </span>
      </div>
      <div className="flex items-center gap-4">
        <button
          type="button"
          onClick={onReset}
          disabled={isSaving}
          className="text-sm font-medium text-[#7A624E] hover:text-[#4A3B2F] transition cursor-pointer"
        >
          Discard
        </button>
        <button
          type="button"
          onClick={onSave}
          disabled={isSaving}
          className="px-4 py-2 text-xs font-semibold text-white bg-[#1D2A44] hover:bg-[#162034] rounded-xl transition cursor-pointer"
        >
          {isSaving ? "Saving..." : "Save now"}
        </button>
      </div>
    </div>
  );
}