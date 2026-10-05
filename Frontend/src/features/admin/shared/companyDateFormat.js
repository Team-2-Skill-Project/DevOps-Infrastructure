export function formatDisplayDate(value) {
  const date = new Date(`${value.slice(0, 10)}T12:00:00`);
  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(date);
}

export function getRelativeLabel(value) {
  const date = new Date(`${value.slice(0, 10)}T12:00:00`);
  const elapsedDays = Math.floor((Date.now() - date.getTime()) / 86_400_000);

  if (elapsedDays <= 0) return "Today";
  if (elapsedDays === 1) return "Yesterday";
  if (elapsedDays < 7) return `${elapsedDays} days ago`;
  if (elapsedDays < 30) return `${Math.floor(elapsedDays / 7)} weeks ago`;
  if (elapsedDays < 365) return `${Math.floor(elapsedDays / 30)} months ago`;
  return `${Math.floor(elapsedDays / 365)} years ago`;
}
