@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center mx-1 px-4 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class='relative inline-flex items-center mx-1 px-4 py-1 text-sm font-medium text-purple-900 border-2 bg-purple-100 border-purple-700 rounded shadow-md focus:outline-none focus:ring focus:ring-violet-300 hover:bg-purple-200 hover:text-black-900'>
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a a href="{{ $paginator->nextPageUrl() }}" rel="next" class='relative inline-flex items-center mx-1 px-4 py-1 text-sm font-medium text-purple-900 border-2 bg-purple-100 border-purple-700 rounded shadow-md focus:outline-none focus:ring focus:ring-violet-300 hover:bg-purple-200 hover:text-black-900'>
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="relative inline-flex items-center mx-1 px-4 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
