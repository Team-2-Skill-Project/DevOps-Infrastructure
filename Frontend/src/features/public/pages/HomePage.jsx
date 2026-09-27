import React from 'react'
import HeroSection from '../components/HomePage/HeroSection/HeroSection';
import LatestJobsSection from '../components/HomePage/LatestJobsSection';
import PhilosophySection from '../components/HomePage/PhilosophySection/PhilosophySection';
import CategoriesSection from '../components/HomePage/CategoriesSection/CategoriesSection';
import WhyMatchInSection from '../components/HomePage/WhyMatchIn/WhyMatchInSection';
import FinalCTASection from '../components/HomePage/FinalCTASection';
import Header from '../components/HomePage/Header';
import Footer from '../components/HomePage/Footer';
// import PricingSection from './components/PricingSection';

export default function HomePage() {
  return <div>
    <Header/>
    <HeroSection />
    <LatestJobsSection />
    <PhilosophySection/>
    <CategoriesSection />
    <WhyMatchInSection />
    {/* <PricingSection/> */}
    <FinalCTASection />
    <Footer/>
  </div>;
}
