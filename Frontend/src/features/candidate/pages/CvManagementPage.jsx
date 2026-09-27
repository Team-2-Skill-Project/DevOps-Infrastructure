import { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";

import ActionBanner from "@/components/shared/ActionBanner";
import CvExtractionReview from "../components/CvManagementPage/CvExtractionReview";
import CvFileCard from "../components/CvManagementPage/CvFileCard";

const ACCEPTED_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
];
const MAX_SIZE_MB = 5;

const MOCK_CV = {
  fileName: "Ahmed_Mahmoud_CV_2026.pdf",
  uploadedAt: "September 8, 2026",
  size: "1.2 MB",
};

const MOCK_SKILLS = [
  {
    id: "1",
    name: "React.js & Redux Toolkit",
    evidence: "Mentioned under Senior Developer role at TechCorp",
    confirmed: false,
  },
];

const ease = [0.16, 1, 0.3, 1];

/**
 * CV Management page — thin orchestrator.
 * Layout: ActionBanner → header → CvFileCard → CvExtractionReview
 */
export default function CvManagementPage() {
  const [banner, setBanner] = useState({ status: null, text: "" });
  const [cv, setCv] = useState(MOCK_CV);
  const [skills, setSkills] = useState(MOCK_SKILLS);
  const [extractionComplete, setExtractionComplete] = useState(true);

  const showBanner = (status, text) => setBanner({ status, text });

  const handleFileSelect = async (file) => {
    const isAccepted =
      ACCEPTED_TYPES.includes(file.type) ||
      /\.(pdf|doc|docx)$/i.test(file.name);

    if (!isAccepted || file.size > MAX_SIZE_MB * 1024 * 1024) {
      showBanner(
        "error",
        "Unsupported file format! Please upload PDF or DOCX files under 5MB only.",
      );
      return;
    }

    showBanner("loading", "Uploading and extracting skills from your CV…");
    setExtractionComplete(false);

    await new Promise((r) => setTimeout(r, 1800));

    setCv({
      fileName: file.name,
      uploadedAt: new Date().toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      }),
      size: `${(file.size / (1024 * 1024)).toFixed(1)} MB`,
    });
    setSkills(MOCK_SKILLS.map((s) => ({ ...s, confirmed: false })));
    setExtractionComplete(true);
    showBanner("success", "CV uploaded and AI extraction completed.");
  };

  const confirmSkill = (id) => {
    showBanner("loading", "Confirming skill…");
    setTimeout(() => {
      setSkills((prev) =>
        prev.map((s) => (s.id === id ? { ...s, confirmed: true } : s)),
      );
      showBanner("success", "Skill confirmed successfully.");
    }, 600);
  };

  const confirmAll = () => {
    showBanner("loading", "Confirming all extracted data…");
    setTimeout(() => {
      setSkills((prev) => prev.map((s) => ({ ...s, confirmed: true })));
      showBanner("success", "All extracted data confirmed.");
    }, 800);
  };

  return (
    <main className="mx-auto w-full max-w-[1280px] flex-1 space-y-6 px-4 py-8 md:px-8">
      <div id="action-banner-slot">
        <ActionBanner status={banner.status} text={banner.text} />
      </div>

      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.45, ease }}
        className="space-y-6 rounded-2xl border border-border bg-surface p-6 shadow-sm"
      >
        {/* Page header */}
        <div className="flex items-center justify-between border-b border-border pb-4">
          <div>
            <h1 className="text-2xl font-bold text-primary">
              CV Management & AI Extraction Review
            </h1>
            <p className="text-xs text-muted">
              Upload your resume, review, and verify the AI-extracted data.
            </p>
          </div>

          <AnimatePresence mode="wait">
            {extractionComplete && (
              <motion.div
                key="extraction-badge"
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.9 }}
                transition={{ duration: 0.2, ease }}
              >
                <Badge className="flex items-center gap-1 rounded-full border-0 bg-success/10 px-3 py-1 text-xs font-bold text-success hover:bg-success/10">
                  <CheckCircle2 className="h-4 w-4" />
                  Extraction Complete
                </Badge>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        <CvFileCard
          fileName={cv.fileName}
          uploadedAt={cv.uploadedAt}
          size={cv.size}
          onFileSelect={handleFileSelect}
        />

        <CvExtractionReview
          skills={skills}
          onConfirmSkill={confirmSkill}
          onConfirmAll={confirmAll}
        />
      </motion.div>
    </main>
  );
}
