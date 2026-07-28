@props([
    'name' => 'uraian_rekom',
    'value' => '',
    'height' => 320,
    'required' => false,
    'placeholder' => 'Tuliskan instruksi rekomendasi secara spesifik dan terukur...',
])

<textarea name="{{ $name }}" id="{{ $name }}-editor" rows="5"
    @if($required) required @endif
    placeholder="{{ $placeholder }}"
    class="shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ $value }}</textarea>

@push('scripts')
    <script src="{{ asset('tinymce/tinymce.min.js') }}"></script>
    <script>
    (function() {
        const selector = '#{{ $name }}-editor';
        const el = document.querySelector(selector);
        if (!el) { console.warn('TinyMCE: target not found', selector); return; }
        if (el.closest('[x-data]') && typeof Alpine !== 'undefined') {
            Alpine.$nextTick(() => {
                tinymce.init({
                    selector: selector,
                    height: {{ $height }},
                    plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link | table | removeformat | fullscreen',
                    menubar: false,
                    statusbar: false,
                    branding: false,
                    elementpath: false,
                    promotion: false,
                    block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(el.value || '');
                        });
                    },
                });
            });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                tinymce.init({
                    selector: selector,
                    height: {{ $height }},
                    plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link | table | removeformat | fullscreen',
                    menubar: false,
                    statusbar: false,
                    branding: false,
                    elementpath: false,
                    promotion: false,
                    block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(el.value || '');
                        });
                    },
                });
            });
        }
    })();
    </script>
@endpush
