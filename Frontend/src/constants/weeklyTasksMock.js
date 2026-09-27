export const WEEKLY_TASKS = [
  {
    id: "task-1",
    title: 'Watch "Scaling Web Applications" module',
    targetSkill: "System Design",
    done: true,
    completedDate: "Aug 30",
    description:
      "A 40-minute video module covering horizontal vs. vertical scaling, load balancing, and caching strategies for high-traffic web applications.",
    why: "System Design was flagged as a high-priority gap because it appears in 9 of 10 roles you've saved and isn't yet confirmed on your profile. This module builds the core vocabulary used throughout the rest of this phase.",
    outcome:
      "You'll be able to explain the trade-offs between scaling strategies and describe how caching reduces load on a backend system.",
  },
  {
    id: "task-2",
    title: "Design a URL-shortener system on paper",
    targetSkill: "System Design",
    done: true,
    completedDate: "Sep 1",
    // Placeholder — replace with the real task copy.
    description:
      "Sketch a URL-shortener end to end: the API surface, the encoding scheme, storage, and how you'd handle collisions at scale.",
    why: "This is the most common warm-up system design question in interviews — practicing it end to end cements the vocabulary from the video module.",
    outcome:
      "You'll be able to walk through a full system design on a whiteboard without freezing on where to start.",
  },
  {
    id: "task-3",
    title: 'Read "Designing Data-Intensive Applications" — Ch. 1–2',
    targetSkill: "System Design",
    done: true,
    completedDate: "Sep 3",
    // Placeholder — replace with the real task copy.
    description:
      "Read the first two chapters, covering reliability, scalability, maintainability, and the different data models used in modern systems.",
    why: "These chapters are the theoretical backbone for every later system design task in this phase.",
    outcome: "You'll be able to define reliability, scalability, and maintainability in your own words with examples.",
  },
  {
    id: "task-4",
    title: "Design a rate limiter and present your trade-offs",
    targetSkill: "System Design",
    done: false,
    completedDate: null,
    // Placeholder — replace with the real task copy.
    description:
      "Design a rate limiter for a public API. Compare at least two algorithms (e.g. token bucket vs. sliding window) and justify a choice.",
    why: "Rate limiting shows up constantly in both interviews and real backend work, and it directly builds on the scaling concepts from task 1.",
    outcome:
      "You'll be able to compare rate-limiting algorithms and defend a choice based on accuracy, memory, and distributed-system constraints.",
  },
];
