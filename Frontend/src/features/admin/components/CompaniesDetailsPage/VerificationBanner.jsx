import { VERIFICATION_STATUS, VERIFICATION_TONES } from "../../shared/companiesDetailsData";

/**
 * VerificationBanner
 * Single status banner driven by the verification config map. The three
 * former copy-pasted banners collapse into one render driven by `status`.
 */
export default function VerificationBanner({ status, title, description }) {
  const config = VERIFICATION_STATUS[status] ?? VERIFICATION_STATUS.pending;
  const tone = VERIFICATION_TONES[config.tone];
  const Icon = config.icon;

  return (
    <div
      role="status"
      className={`flex items-center gap-3 rounded-2xl border px-4.5 py-3.5 ${tone.banner}`}
    >
      <div
        className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${tone.icon}`}
      >
        <Icon className="h-5 w-5" />
      </div>
      <div>
        <div className={`text-[14.5px] font-bold ${tone.title}`}>{title}</div>
        <div className="mt-0.5 text-[13px] text-muted">{description}</div>
      </div>
    </div>
  );
}