@if ($paginator->hasPages())
<div class="flex items-center gap-1 sm:gap-1.5">
    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
        <button disabled class="shadow-theme-xs mr-1.5 sm:mr-2.5 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-400 cursor-not-allowed dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-600">
            Previous
        </button>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="shadow-theme-xs mr-1.5 sm:mr-2.5 flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
            Previous
        </a>
    @endif

    {{-- Desktop Pagination Elements --}}
    <ul class="flex items-center gap-1">
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li>
                    <span class="flex h-9 w-9 items-center justify-center text-xs font-semibold text-gray-400 dark:text-gray-500">
                        {{ $element }}
                    </span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li>
                        @if ($page == $paginator->currentPage())
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white shadow-sm shadow-blue-500/20">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-xs font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    </li>
                @endforeach
            @endif
        @endforeach
    </ul>

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="shadow-theme-xs ml-1.5 sm:ml-2.5 flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
            Next
        </a>
    @else
        <button disabled class="shadow-theme-xs ml-1.5 sm:ml-2.5 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-400 cursor-not-allowed dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-600">
            Next
        </button>
    @endif
</div>
@endif