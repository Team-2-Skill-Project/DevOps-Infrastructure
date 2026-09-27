import * as React from "react";
import { forwardRef, useState } from "react";
import { Lock, Eye, EyeOff } from "lucide-react";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";


export const PasswordInput = forwardRef(function PasswordInput(
  { className, showLock = false, ...props },
  ref,
) {
  const [visible, setVisible] = useState(false);

  return (
    <div className="relative">
      {showLock && (
        <Lock className="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
      )}
      <Input
        ref={ref}
        type={visible ? "text" : "password"}
        className={cn(
          "h-11.5 w-full rounded-[8px] border border-[#e2e8f0] bg-white py-3.5 text-[16px] text-[#0f172a] outline-none transition-all placeholder:text-[#e2e8f0] focus-visible:border-[#1d3557]",
          showLock ? "ps-9 pe-10" : "px-4 pe-11",
          className,
        )}
        {...props}
      />
      <button
        type="button"
        onClick={() => setVisible((v) => !v)}
        aria-label={visible ? "Hide password" : "Show password"}
        className="absolute inset-e-3.5 top-1/2 -translate-y-1/2 text-[#94a3b8] hover:text-[#0f172a] transition-colors cursor-pointer"
      >
        {visible ? (
          <EyeOff className="h-4.5 w-4.5" />
        ) : (
          <Eye className="h-4.5 w-4.5" />
        )}
      </button>
    </div>
  );
});

export default PasswordInput;
