@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            {{-- Mobile pagination --}}
            <div class="flex flex-1 justify-between sm:hidden">
                @if ($paginator->onFirstPage())
                    <flux:button variant="ghost" disabled>
                        {!! __('pagination.previous') !!}
                    </flux:button>
                @else
                    <flux:button
                        variant="ghost"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                    >
                        {!! __('pagination.previous') !!}
                    </flux:button>
                @endif

                @if ($paginator->hasMorePages())
                    <flux:button
                        variant="ghost"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                    >
                        {!! __('pagination.next') !!}
                    </flux:button>
                @else
                    <flux:button variant="ghost" disabled>
                        {!! __('pagination.next') !!}
                    </flux:button>
                @endif
            </div>

            {{-- Desktop pagination --}}
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                        {!! __('Showing') !!}
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                        {!! __('of') !!}
                        <span class="font-medium">{{ $paginator->total() }}</span>
                        {!! __('results') !!}
                    </flux:text>
                </div>

                <div class="flex items-center space-x-1">
                    {{-- Previous Button --}}

                    @if ($paginator->onFirstPage())
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="chevron-left"
                            disabled
                            aria-label="{{ __('pagination.previous') }}"
                        />
                    @else
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="chevron-left"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            aria-label="{{ __('pagination.previous') }}"
                        />
                    @endif

                    {{-- Page Numbers --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <flux:button variant="primary" size="sm" aria-current="page">
                                        {{ $page }}
                                    </flux:button>
                                @else
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    >
                                        {{ $page }}
                                    </flux:button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Button --}}

                    @if ($paginator->hasMorePages())
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="chevron-right"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            aria-label="{{ __('pagination.next') }}"
                        />
                    @else
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="chevron-right"
                            disabled
                            aria-label="{{ __('pagination.next') }}"
                        />
                    @endif
                </div>
            </div>
        </nav>
    @endif
</div>
