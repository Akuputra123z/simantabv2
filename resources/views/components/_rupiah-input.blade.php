{{-- resources/views/components/_rupiah-input.blade.php --}}
<script>
window.RupiahInput = (function () {
    // Membersihkan segala karakter kecuali angka
    function clean(val) {
        return String(val || '0').replace(/\D/g, '');
    }

    function fmt(val) {
        const num = clean(val);
        if (!num || num === '0') return '';
        return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function init(displayEl, opts = {}) {
        const { name, value = 0 } = opts;

        let hiddenEl = displayEl.parentElement.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!hiddenEl) {
            hiddenEl = document.createElement('input');
            hiddenEl.type = 'hidden';
            hiddenEl.name = name;
            displayEl.parentElement.appendChild(hiddenEl);
        }

        // SET NILAI AWAL DARI DATABASE
        const initialRaw = clean(value);
        hiddenEl.value = initialRaw;
        displayEl.value = initialRaw !== '0' ? fmt(initialRaw) : '';

        displayEl.addEventListener('input', function() {
            let cursor = this.selectionStart;
            const prevLen = this.value.length;
            
            const raw = clean(this.value);
            const formatted = fmt(raw);
            
            this.value = formatted;

            // Jaga posisi kursor
            const diff = formatted.length - prevLen;
            this.setSelectionRange(cursor + diff, cursor + diff);

            hiddenEl.value = raw;
            // Penting: Beritahu Alpine kalau data berubah
            displayEl.dispatchEvent(new Event('change', { bubbles: true }));
        });

        displayEl._hiddenEl = hiddenEl;
    }

    function initAll(container = document) {
        container.querySelectorAll('.rupiah-field:not([data-ri-init])').forEach(el => {
            el.setAttribute('data-ri-init', '1');
            init(el, {
                name: el.dataset.name,
                value: el.dataset.value
            });
        });
    }

    return { initAll, fmt };
})();
document.addEventListener('DOMContentLoaded', () => window.RupiahInput.initAll());
</script>