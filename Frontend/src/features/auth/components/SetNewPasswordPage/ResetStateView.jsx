import { motion } from "framer-motion";

const toneClasses = {
  blue: "bg-sky-100 text-sky-600",
  red: "bg-red-100 text-red-500",
  green: "bg-emerald-100 text-emerald-500",
};

export default function ResetStateView({ tone, icon, title, desc, children }) {
  return (
    <motion.div
      key={title}
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      transition={{ duration: 0.3, ease: "easeInOut" }}
      className="flex w-full flex-col items-center text-center"
    >
      <div
        className={`mb-4 flex h-16 w-16 items-center justify-center rounded-full text-2xl ${toneClasses[tone]}`}
      >
        {icon}
      </div>
      <h2 className="mb-2 text-xl font-bold text-slate-900">{title}</h2>
      <p className="mb-6 text-[13px] leading-relaxed text-slate-500">{desc}</p>
      {children}
    </motion.div>
  );
}
