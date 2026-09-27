import { motion } from "framer-motion";

export default function MatchRing({ score }) {
  const radius = 16;
  const circumference = 2 * Math.PI * radius;
  const strokeDashoffset = circumference - (score / 100) * circumference;

  let strokeColor = "#4F7A5A";
  let textColor = "text-success";
  if (score < 90 && score >= 75) {
    strokeColor = "#C88A26";
    textColor = "text-warning";
  } else if (score < 75) {
    strokeColor = "#707780";
    textColor = "text-muted";
  }

  return (
    <div className="relative w-10 h-10 flex items-center justify-center shrink-0">
      <svg className="w-10 h-10 transform -rotate-90">
        <circle
          cx="20"
          cy="20"
          r={radius}
          stroke="#E5E1DA"
          strokeWidth="3.5"
          fill="transparent"
        />
        <motion.circle
          cx="20"
          cy="20"
          r={radius}
          stroke={strokeColor}
          strokeWidth="3.5"
          strokeDasharray={circumference}
          initial={{ strokeDashoffset: circumference }}
          animate={{ strokeDashoffset }}
          transition={{ duration: 1, ease: "easeOut" }}
          strokeLinecap="round"
          fill="transparent"
        />
      </svg>
      <span className={`absolute text-[10px] font-bold ${textColor}`}>
        {score}%
      </span>
    </div>
  );
}
