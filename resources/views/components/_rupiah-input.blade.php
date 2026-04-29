{{-- resources/views/components/_rupiah-input.blade.php --}}
<script>
window.RupiahInput = (function () {
    /**
     * Membersihkan string dari karakter non-digit.
     * Mengembalikan string angka murni.
     */
    function parseRaw(val) {
        if (typeof val === 'number') return String(val);
        return String(val || '0').replace(/\D/g, '') || '0';
    }

    /**
     * Memformat angka menjadi format ribuan Indonesia (titik).
     */
    function formatIDR(val) {
        const raw = parseRaw(val);
        if (raw === '0') return '';
        return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    /**
     * Inisialisasi elemen input tunggal
     */
    function init(displayEl, opts = {}) {
        const name = opts.name || displayEl.dataset.name;
        const initialValue = opts.value || displayEl.dataset.value || '0';

        // Pastikan input mode numerik untuk keyboard HP yang nyaman
        if (!displayEl.getAttribute('inputmode')) {
            displayEl.setAttribute('inputmode', 'numeric');
        }

        // Cari atau buat input hidden untuk menyimpan nilai asli (integer)
        let hiddenEl = displayEl.parentElement.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!hiddenEl && name) {
            hiddenEl = document.createElement('input');
            hiddenEl.type = 'hidden';
            hiddenEl.name = name;
            displayEl.parentElement.appendChild(hiddenEl);
        }

        // Set Nilai Awal
        const rawInitial = parseRaw(initialValue);
        if (hiddenEl) hiddenEl.value = rawInitial;
        displayEl.value = formatIDR(rawInitial);

        // Handler Input
        displayEl.addEventListener('input', function(e) {
            const originalValue = this.value;
            const selectionStart = this.selectionStart;
            
            const raw = parseRaw(originalValue);
            const formatted = formatIDR(raw);
            
            this.value = formatted;

            // Hitung posisi kursor yang baru agar tidak melompat
            // Menghitung berapa banyak karakter non-digit sebelum kursor
            let newCursorPos = selectionStart;
            const beforeCursor = originalValue.substring(0, selectionStart);
            const digitsBefore = beforeCursor.replace(/\D/g, '').length;
            
            let countDigits = 0;
            let i = 0;
            for (i = 0; i < formatted.length && countDigits < digitsBefore; i++) {
                if (/\d/.test(formatted[i])) countDigits++;
            }
            newCursorPos = i;

            this.setSelectionRange(newCursorPos, newCursorPos);

            // Update Hidden Input
            if (hiddenEl) {
                hiddenEl.value = raw;
                // Dispatch event agar didengar oleh script luar (seperti hitung total otomatis)
                hiddenEl.dispatchEvent(new Event('change', { bubbles: true }));
                hiddenEl.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        // Handler saat blur: rapikan jika kosong
        displayEl.addEventListener('blur', function() {
            if (parseRaw(this.value) === '0') {
                this.value = '';
                if (hiddenEl) hiddenEl.value = '0';
            }
        });
    }

    /**
     * Scan seluruh DOM atau container tertentu
     */
    function initAll(container = document) {
        container.querySelectorAll('.rupiah-field:not([data-ri-init])').forEach(el => {
            el.setAttribute('data-ri-init', '1');
            init(el);
        });
    }

    return { 
        initAll, 
        fmt: formatIDR,
        parse: parseRaw 
    };
})();

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => window.RupiahInput.initAll());
</script>