import WhyMatchInContent from "./WhyMatchInContent";
import WhyMatchInVisual from "./WhyMatchInVisual";


export default function WhyMatchInSection() {
  return (
    <section id="benefits" className="w-full scroll-mt-20 bg-background py-16 lg:py-24">
      <div className="mx-auto max-w-[1280px] px-6 md:px-10 lg:px-16">
        <div className="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-16">
          <WhyMatchInContent />
          <WhyMatchInVisual />
        </div>
      </div>
    </section>
  );
}
