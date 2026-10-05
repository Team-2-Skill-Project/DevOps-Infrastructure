import React from "react";
import { AnimatePresence, motion } from "framer-motion";
import { translateValidationMessage } from "@/components/shared/i18n/validationMessage";

export default function SequentialFormMessage({
  name,
  errors = {},
  firstErrorField,
}) {
  const activeField = firstErrorField ?? Object.keys(errors)[0];
  const isActive = activeField === name;

  return (
    <AnimatePresence mode="wait">
      {isActive && errors[name] && (
        <motion.div
          key={name}
          initial={{ opacity: 0, y: -4, height: 0 }}
          animate={{ opacity: 1, y: 0, height: "auto" }}
          exit={{ opacity: 0, y: -4, height: 0 }}
          transition={{ duration: 0.18, ease: "easeOut" }}
        >
          <p className="text-[11px] text-error">
            {translateValidationMessage(errors[name].message)}
          </p>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
