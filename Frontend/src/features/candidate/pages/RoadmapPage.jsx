import { useState } from "react";
import { 
  EmptyProfileState, 
  NoRoadmapState, 
  GeneratingState, 
  ErrorState 
} from "../components/RoadmapPage/RoadmapStates";
import RoadmapBoard from "../components/RoadmapPage/RoadmapBoard";

export default function RoadmapPage() {
  // Available states: 'empty-profile', 'no-roadmap', 'generating', 'error', 'generated'
  const [view, setView] = useState("no-roadmap");
  const [isUpdating, setIsUpdating] = useState(false);

  const handleGenerate = () => {
    setView("generating");
    // Simulate API call delay
    setTimeout(() => setView("generated"), 2200);
  };

  const handleRefresh = () => {
    setIsUpdating(true);
    // Simulate recalculation delay
    setTimeout(() => setIsUpdating(false), 2000);
  };

  return (
    <main className="mx-auto max-w-[960px] px-5 py-8">
      {view === "empty-profile" && (
        <EmptyProfileState 
          onUploadCv={() => {}} 
          onCompleteProfile={() => {}} 
        />
      )}
      
      {view === "no-roadmap" && (
        <NoRoadmapState onGenerate={handleGenerate} />
      )}
      
      {view === "generating" && (
        <GeneratingState />
      )}
      
      {view === "error" && (
        <ErrorState onRetry={handleGenerate} />
      )}
      
      {view === "generated" && (
        <RoadmapBoard 
          isUpdating={isUpdating}
          onRefresh={handleRefresh}
          onChangeRole={() => setView("no-roadmap")}
        />
      )}
    </main>
  );
}
