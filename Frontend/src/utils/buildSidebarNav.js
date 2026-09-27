
export function buildSidebarNav(routes, basePath = "") {
  return routes
    .filter((route) => route.handle?.sidebar)
    .map((route) => ({
      label: route.handle.label,
      labelKey: route.handle.labelKey,
      icon: route.handle.icon,
      href: route.index ? basePath : `${basePath}/${route.path}`.replace(/\/{2,}/g, "/"),
      // "end" tells NavLink to match this path exactly (not prefixes),
      // which matters most for the index route ("/dashboard").
      end: Boolean(route.index),
    }));
}
