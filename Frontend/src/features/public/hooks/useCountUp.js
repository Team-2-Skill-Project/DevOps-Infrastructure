import { useRef, useState, useEffect } from "react";
import { useInView, animate } from "framer-motion";

/**
 * Animates a number from 0 -> target once the element scrolls into view.
 * @param {number} target - final value to count up to
 * @param {{ decimals?: number, duration?: number }} options
 */
export function useCountUp(target, { decimals = 0, duration = 1.4 } = {}) {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, amount: 0.3 });
  const [value, setValue] = useState(0);

  useEffect(() => {
    if (!isInView) return;

    const controls = animate(0, target, {
      duration,
      ease: [0.16, 1, 0.3, 1],
      onUpdate: (latest) => setValue(Number(latest.toFixed(decimals))),
    });

    return () => controls.stop();
  }, [isInView, target, duration, decimals]);

  return { ref, value };
}
