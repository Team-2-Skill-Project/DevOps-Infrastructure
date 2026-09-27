import React from "react";
import UserDashboardHeader from "../components/Overview/UserDashboardHeader";
import PipelineOverviewCard from "../components/Overview/PipelineOverviewCard";
import RecommendedJobsCard from "../components/Overview/RecommendedJobsCard";
import RoadmapCard from "../components/Overview/RoadmapCard";
import AIMentorCard from "../components/Overview/AIMentorCard";
import SavedJobsCard from "../components/Overview/SavedJobsCard";
import NotificationsCard from "../components/Overview/NotificationsCard";
import CompetencyRadarCard from "../components/Overview/CompetencyRadarCard";
import PrioritySkillGapsCard from "../components/Overview/PrioritySkillGapsCard";

export default function Overview() {
  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
     
      <div className="lg:col-span-3">
        <UserDashboardHeader />
      </div>

      
      <div className="lg:col-span-2 flex flex-col gap-6">
        <PipelineOverviewCard />
        <RecommendedJobsCard />
        <PrioritySkillGapsCard />
      </div>

      <div className="lg:col-span-1 flex flex-col gap-6">
        <CompetencyRadarCard />
        <AIMentorCard />
        <RoadmapCard />
        <NotificationsCard />
        <SavedJobsCard />
      </div>
    </div>
  );
}
