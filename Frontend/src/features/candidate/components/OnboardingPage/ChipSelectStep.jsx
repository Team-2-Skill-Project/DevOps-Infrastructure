import { useState } from "react";
import { Search, ArrowLeft, ArrowRight } from "lucide-react";
import { Button } from "@/components/ui/button";
import { requiredFieldSchema } from "../../schema/onboardingSchema";

export default function ChipSelectStep({
  stepConfig,
  value,
  onContinue,
  onPrevious,
  isFirstStep,
}) {
  const {
    field,
    title,
    subtitle,
    inputLabel,
    placeholder,
    suggestionsLabel,
    suggestions,
  } = stepConfig;
  const [inputValue, setInputValue] = useState(value || "");
  const [error, setError] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    const result = requiredFieldSchema.safeParse(inputValue);
    if (!result.success) {
      setError(result.error.issues[0].message);
      return;
    }
    setError("");
    onContinue(field, result.data);
  };

  return (
    <div>
      <h2 className="mb-1.5 text-[20px] font-bold text-ink">{title}</h2>
      <p className="mb-6 text-[13px] text-muted">{subtitle}</p>

      <form onSubmit={handleSubmit}>
        <div className="mb-6">
          <label
            htmlFor={field}
            className="mb-1.5 block text-[13px] font-semibold text-ink"
          >
            {inputLabel}
          </label>
          <div className="relative">
            <Search className="absolute inset-s-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <input
              id={field}
              type="text"
              value={inputValue}
              onChange={(e) => {
                setInputValue(e.target.value);
                if (error) setError("");
              }}
              placeholder={placeholder}
              className={`w-full rounded-xl border bg-surface py-3 ps-10 pe-3.5 text-[14px] text-ink outline-none transition-colors placeholder:text-muted focus:border-primary ${
                error ? "border-error" : "border-border"
              }`}
            />
          </div>
          {error && (
            <p className="mt-1.5 text-[12px] font-medium text-error">{error}</p>
          )}
        </div>

        <div className="mb-8">
          <span className="mb-2 block text-[12px] font-medium text-muted">
            {suggestionsLabel}
          </span>
          <div className="flex flex-wrap gap-2">
            {suggestions.map((option) => (
              <button
                key={option}
                type="button"
                onClick={() => {
                  setInputValue(option);
                  if (error) setError("");
                }}
                className={`rounded-full border px-3.5 py-1.5 text-[12.5px] font-medium transition-colors ${
                  inputValue === option
                    ? "border-primary bg-primary text-primary-foreground"
                    : "border-border bg-surface text-muted hover:border-primary/40 hover:text-primary"
                }`}
              >
                {option}
              </button>
            ))}
          </div>
        </div>

        <div className="flex items-center gap-3">
          <Button
            type="button"
            variant="outline"
            onClick={onPrevious}
            disabled={isFirstStep}
            className="h-11 flex-1 gap-1.5 rounded-xl border-border text-[13px] font-bold text-muted disabled:cursor-not-allowed disabled:opacity-50"
          >
            <ArrowLeft className="h-4 w-4" />
            Previous
          </Button>
          <Button
            type="submit"
            className="h-11 flex-1 gap-1.5 rounded-xl bg-primary text-[13px] font-bold text-primary-foreground hover:bg-primary/90"
          >
            Continue
            <ArrowRight className="h-4 w-4" />
          </Button>
        </div>
      </form>
    </div>
  );
}
