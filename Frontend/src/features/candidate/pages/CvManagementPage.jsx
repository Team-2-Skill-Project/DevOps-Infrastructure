import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, Sparkles } from "lucide-react";
import { Badge } from "@/components/ui/badge";

import ActionBanner from "@/components/shared/ActionBanner";
import CvExtractionReview from "../components/CvManagementPage/CvExtractionReview";
import CvFileCard from "../components/CvManagementPage/CvFileCard";
import { DEFAULT_PARSED_CV } from "../shared/cvExtractionMock";

const ACCEPTED_TYPES = [
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
];
const MAX_SIZE_MB = 10;

const INITIAL_CV = {
  fileName: "Samir_Salah_CV.pdf",
  uploadedAt: "October 4, 2026",
  size: "2.4 MB",
};

const ease = [0.16, 1, 0.3, 1];

/**
 * CV Management page — orchestrates CV upload and AI extraction review.
 * Displays full backend parsed CV data: candidate profile, work experiences,
 * education, skills with confidence/evidence, and languages.
 */
export default function CvManagementPage() {
  const { t } = useTranslation("dashboard");
  const [banner, setBanner] = useState({ status: null, text: "" });
  const [cv, setCv] = useState(INITIAL_CV);
  const [extractionData, setExtractionData] = useState(DEFAULT_PARSED_CV);
  const [extractionComplete, setExtractionComplete] = useState(true);

  const showBanner = (status, text) => setBanner({ status, text });

  const handleFileSelect = async (file) => {
    const isAccepted =
      ACCEPTED_TYPES.includes(file.type) ||
      /\.(pdf|doc|docx)$/i.test(file.name);

    if (!isAccepted || file.size > MAX_SIZE_MB * 1024 * 1024) {
      showBanner(
        "error",
        "Unsupported file format! Please upload PDF or DOCX files under 10MB only.",
      );
      return;
    }

    showBanner("loading", "Uploading and analyzing CV with AI extraction models…");
    setExtractionComplete(false);

    // Simulate AI parsing delay
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

    // Reset confirmed states on fresh upload
    setExtractionData((prev) => ({
      ...prev,
      profileConfirmed: false,
      candidate_skills: prev.candidate_skills.map((s) => ({
        ...s,
        confirmed: false,
      })),
      experiences: prev.experiences.map((e) => ({
        ...e,
        confirmed: false,
      })),
      educations: prev.educations.map((ed) => ({
        ...ed,
        confirmed: false,
      })),
      languages: prev.languages.map((l) => ({
        ...l,
        confirmed: false,
      })),
    }));

    setExtractionComplete(true);
    showBanner("success", "CV uploaded and AI extraction completed successfully.");
  };

  const confirmSkill = (id) => {
    setExtractionData((prev) => ({
      ...prev,
      candidate_skills: prev.candidate_skills.map((s) =>
        s.skill_id === id || s.id === id ? { ...s, confirmed: true } : s,
      ),
    }));
    showBanner("success", "Skill confirmed successfully.");
  };

  const confirmExperience = (id) => {
    setExtractionData((prev) => ({
      ...prev,
      experiences: prev.experiences.map((e, idx) =>
        e.id === id || idx === id ? { ...e, confirmed: true } : e,
      ),
    }));
    showBanner("success", "Experience record confirmed.");
  };

  const confirmEducation = (id) => {
    setExtractionData((prev) => ({
      ...prev,
      educations: prev.educations.map((ed, idx) =>
        ed.id === id || idx === id ? { ...ed, confirmed: true } : ed,
      ),
    }));
    showBanner("success", "Education record confirmed.");
  };

  const confirmLanguage = (id) => {
    setExtractionData((prev) => ({
      ...prev,
      languages: prev.languages.map((l, idx) =>
        l.id === id || idx === id ? { ...l, confirmed: true } : l,
      ),
    }));
    showBanner("success", "Language verified.");
  };

  const confirmProfile = () => {
    setExtractionData((prev) => ({
      ...prev,
      profileConfirmed: true,
    }));
    showBanner("success", "Profile details verified.");
  };

  const confirmAll = () => {
    showBanner("loading", "Confirming all extracted sections…");
    setTimeout(() => {
      setExtractionData((prev) => ({
        ...prev,
        profileConfirmed: true,
        candidate_skills: prev.candidate_skills.map((s) => ({
          ...s,
          confirmed: true,
        })),
        experiences: prev.experiences.map((e) => ({
          ...e,
          confirmed: true,
        })),
        educations: prev.educations.map((ed) => ({
          ...ed,
          confirmed: true,
        })),
        languages: prev.languages.map((l) => ({
          ...l,
          confirmed: true,
        })),
      }));
      showBanner("success", "All extracted CV data confirmed and ready to sync with profile.");
    }, 600);
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
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-border pb-4">
          <div>
            <h1 className="text-2xl font-bold text-primary">
              {t("pages.cvManagement.title", "CV Management & AI Extraction")}
            </h1>
            <p className="text-xs text-muted">
              {t(
                "pages.cvManagement.description",
                "Upload your resume, review, and verify all AI-extracted information.",
              )}
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
                <Badge className="flex items-center gap-1.5 rounded-full border-0 bg-success/10 px-3.5 py-1.5 text-xs font-bold text-success hover:bg-success/10">
                  <CheckCircle2 className="h-4 w-4" />
                  AI Extraction Complete
                </Badge>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        {/* Upload File Card */}
        <CvFileCard
          fileName={cv.fileName}
          uploadedAt={cv.uploadedAt}
          size={cv.size}
          onFileSelect={handleFileSelect}
        />

        {/* Full Extraction Review with Tabs for all backend data */}
        <CvExtractionReview
          extractionData={extractionData}
          onConfirmSkill={confirmSkill}
          onConfirmExperience={confirmExperience}
          onConfirmEducation={confirmEducation}
          onConfirmLanguage={confirmLanguage}
          onConfirmProfile={confirmProfile}
          onConfirmAll={confirmAll}
        />
      </motion.div>
    </main>
  );
}
