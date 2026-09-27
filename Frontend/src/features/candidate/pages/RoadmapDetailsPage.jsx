import React from 'react'
import RoadmapPhaseHeader from '../components/RoadmapDetailsPage/RoadmapPhaseHeader'
import PhaseSkillsCard from '../components/RoadmapDetailsPage/PhaseSkillsCard'
import MilestonesCard from '../components/RoadmapDetailsPage/MilestonesCard'
import WeeklyTasksCard from '../components/RoadmapDetailsPage/WeeklyTasksCard'

export default function RoadmapDetailsPage() {
  return (
    <div>
      <RoadmapPhaseHeader />
      <PhaseSkillsCard />
      <MilestonesCard />
      <WeeklyTasksCard/>
    </div>
  )
}
