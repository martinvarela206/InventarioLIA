@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between text-xs">
        <div class="flex flex-1 justify-between sm:hidden">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 py-1.5 font-medium text-figma-text-tertiary bg-figma-bg-deep border border-figma-border-subtle cursor-default rounded-lg">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-1.5 font-medium text-figma-text-secondary bg-figma-panel border border-figma-border-subtle rounded-lg hover:text-figma-text-primary transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-1.5 font-medium text-figma-text-secondary bg-figma-panel border border-figma-border-subtle rounded-lg hover:text-figma-text-primary transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="relative inline-flex items-center px-3 py-1.5 font-medium text-figma-text-tertiary bg-figma-bg-deep border border-figma-border-subtle cursor-default rounded-lg">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-figma-text-tertiary">
                    {!! __('Showing') !!}
                    <span class="font-mono font-bold text-figma-text-primary">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-mono font-bold text-figma-text-primary">{{ $paginator->lastItem() }}</span>
                    {!! __('of') !!}
                    <span class="font-mono font-bold text-figma-text-primary">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-xs rounded-lg overflow-hidden border border-figma-border-subtle">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-figma-text-tertiary bg-figma-bg-deep cursor-default" aria-hidden="true">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-figma-text-secondary bg-figma-panel hover:bg-figma-bg-deep hover:text-figma-text-primary transition-colors" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- -10 Pages --}}
                     @if ($paginator->currentPage() > 10)
                        <a href="{{ $paginator->url($paginator->currentPage() - 10) }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-mono font-medium text-figma-text-secondary bg-figma-panel hover:bg-figma-bg-deep border-l border-figma-border-subtle transition-colors">
                            -10
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-mono font-medium text-figma-text-tertiary bg-figma-bg-deep border-l border-figma-border-subtle cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-mono font-extrabold text-black bg-figma-brand border-l border-figma-border-subtle cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-mono font-medium text-figma-text-secondary bg-figma-panel hover:bg-figma-bg-deep border-l border-figma-border-subtle transition-colors" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- +10 Pages --}}
                    @if ($paginator->currentPage() < $paginator->lastPage() - 10)
                        <a href="{{ $paginator->url($paginator->currentPage() + 10) }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-mono font-medium text-figma-text-secondary bg-figma-panel hover:bg-figma-bg-deep border-l border-figma-border-subtle transition-colors">
                            +10
                        </a>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-figma-text-secondary bg-figma-panel hover:bg-figma-bg-deep border-l border-figma-border-subtle transition-colors" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-2 py-1.5 text-xs font-medium text-figma-text-tertiary bg-figma-bg-deep border-l border-figma-border-subtle cursor-default" aria-hidden="true">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
    <div class="sm:hidden flex items-center justify-center mt-2 gap-2 text-xs">
        @if ($paginator->currentPage() > 10)
            <a href="{{ $paginator->url($paginator->currentPage() - 10) }}" class="px-2.5 py-1 border border-figma-border-subtle rounded-lg bg-figma-panel font-mono text-xs text-figma-text-secondary">-10</a>
        @endif
        
        <span class="font-mono text-xs text-figma-text-secondary">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->currentPage() < $paginator->lastPage() - 10)
            <a href="{{ $paginator->url($paginator->currentPage() + 10) }}" class="px-2.5 py-1 border border-figma-border-subtle rounded-lg bg-figma-panel font-mono text-xs text-figma-text-secondary">+10</a>
        @endif
    </div>
@endif
