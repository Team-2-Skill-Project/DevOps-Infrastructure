import { useRef } from "react";

/**
 * Tracks mouse position within an element and writes it to CSS vars
 * 
 */
export function useMouseSpotlight() {
  const ref = useRef(null);

  const handleMouseMove = (e) => {
    const el = ref.current;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    el.style.setProperty("--mouse-x", `${e.clientX - rect.left}px`);
    el.style.setProperty("--mouse-y", `${e.clientY - rect.top}px`);
  };

  return { ref, handleMouseMove };
}
