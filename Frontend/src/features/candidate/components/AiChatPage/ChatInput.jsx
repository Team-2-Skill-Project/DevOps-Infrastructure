import { useRef, useCallback } from "react";
import { motion } from "framer-motion";
import { Send } from "lucide-react";
import { cn } from "@/lib/utils";

/**
 * Chat input with auto-resizing textarea + animated send button.
 *
 * @param {{
 *   value?: string,
 *   onChange?: (value: string) => void,
 *   onSend?: (value: string) => void,
 *   disabled?: boolean,
 *   className?: string,
 * }} props
 */
export default function ChatInput({
  value = "",
  onChange,
  onSend,
  disabled = false,
  className,
}) {
  const textareaRef = useRef(null);

  const handleInput = useCallback(
    (e) => {
      onChange?.(e.target.value);

      // Auto-resize
      const el = e.target;
      el.style.height = "auto";
      el.style.height = `${Math.min(el.scrollHeight, 96)}px`;
    },
    [onChange],
  );

  const handleSend = useCallback(() => {
    const trimmed = value.trim();
    if (!trimmed || disabled) return;
    onSend?.(trimmed);

    // Reset height
    if (textareaRef.current) {
      textareaRef.current.style.height = "auto";
    }
  }, [value, disabled, onSend]);

  const handleKeyDown = useCallback(
    (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    },
    [handleSend],
  );

  const canSend = value.trim().length > 0 && !disabled;

  return (
    <div className={cn("flex-shrink-0 border-t border-border p-4", className)}>
      <div className="flex items-end gap-2 rounded-xl border border-border px-3 py-2 transition-colors focus-within:border-primary focus-within:shadow-[0_0_0_3px_rgba(31,54,92,0.1)]">
        <textarea
          ref={textareaRef}
          rows={1}
          value={value}
          onInput={handleInput}
          onKeyDown={handleKeyDown}
          disabled={disabled}
          placeholder="Ask about your profile, a job, or your roadmap…"
          className="max-h-24 min-h-0 flex-1 resize-none bg-transparent py-1.5 text-[13px] text-ink outline-none placeholder:text-muted disabled:opacity-50"
        />

        <motion.button
          whileHover={canSend ? { scale: 1.08 } : {}}
          whileTap={canSend ? { scale: 0.92 } : {}}
          onClick={handleSend}
          disabled={!canSend}
          className={cn(
            "flex size-9 flex-shrink-0 items-center justify-center rounded-lg transition-all",
            canSend
              ? "bg-primary text-primary-foreground shadow-[0_2px_8px_rgba(31,54,92,0.2)] cursor-pointer"
              : "bg-primary/30 text-primary-foreground/50 cursor-not-allowed",
          )}
        >
          <Send className="size-[18px]" />
        </motion.button>
      </div>

      <p className="mt-1.5 px-1 text-[11px] text-muted">
        Mentor responses are grounded in your account data — not generic advice.
      </p>
    </div>
  );
}
