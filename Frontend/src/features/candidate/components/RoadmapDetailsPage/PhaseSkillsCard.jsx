import { motion } from "framer-motion";
import { useTranslation } from "react-i18next";
import PhaseSkillRow from "./PhaseSkillRow";
import { PHASE_SKILLS } from "@/features/candidate/shared/phaseSkillsMock";

export default function PhaseSkillsCard({ skills = PHASE_SKILLS }) {
  const { t } = useTranslation("dashboard");
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
      className="mb-5 rounded-3xl border border-border bg-surface p-6 shadow-sm"
    >
      <h2 className="mb-1 font-display text-[15px] font-bold text-ink">
        {t("pages.roadmap.phaseSkills")}
      </h2>
      <p className="mb-4 text-[12px] text-muted">
        Every skill here addresses a priority gap from your roadmap — nothing in this phase is
        unlinked busywork.
      </p>

      <div className="space-y-2">
        {skills.map((skill, index) => (
          <PhaseSkillRow key={skill.id} skill={skill} index={index} />
        ))}
      </div>
    </motion.div>
  );
}
