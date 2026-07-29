<div id="preloader"
  class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
  <div
    class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
  ></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var el = document.getElementById('preloader');
        if (el) el.remove();
    }, 350);
});
</script>
@endpush