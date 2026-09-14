<div
  :class="$store.sidebar.isMobileOpen ? 'block xl:hidden' : 'hidden'"
  @click="$store.sidebar.setMobileOpen(false)"
  class="fixed inset-0 z-[99] h-screen w-full bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 cursor-pointer"
></div>
