@props(['content' => ''])

@php
    $parsed = \Illuminate\Support\Str::markdown($content ?? '', [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
        'renderer' => [
            'soft_break' => "<br>\n",
        ],
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'ticket-markdown-prose text-sm text-gray-800 leading-relaxed']) }}>
    {!! $parsed !!}
</div>

@once
<style>
.ticket-markdown-prose {
    word-break: break-word;
}
.ticket-markdown-prose h1, 
.ticket-markdown-prose h2, 
.ticket-markdown-prose h3,
.ticket-markdown-prose h4 {
    font-weight: 800 !important;
    color: #111827 !important;
    margin-top: 0.85rem !important;
    margin-bottom: 0.4rem !important;
    line-height: 1.3 !important;
}
.ticket-markdown-prose h1 { 
    font-size: 1.15rem !important; 
    border-bottom: 2px solid #e5e7eb !important;
    padding-bottom: 0.25rem !important;
}
.ticket-markdown-prose h2 { 
    font-size: 1.05rem !important; 
    border-bottom: 1px solid #f3f4f6 !important;
    padding-bottom: 0.2rem !important;
}
.ticket-markdown-prose h3 { font-size: 0.95rem !important; }
.ticket-markdown-prose h4 { font-size: 0.875rem !important; }
.ticket-markdown-prose p {
    margin-bottom: 0.65rem !important;
    line-height: 1.65 !important;
    color: #374151 !important;
}
.ticket-markdown-prose p:last-child {
    margin-bottom: 0 !important;
}
.ticket-markdown-prose ul {
    list-style-type: disc !important;
    padding-left: 1.4rem !important;
    margin: 0.65rem 0 !important;
}
.ticket-markdown-prose ol {
    list-style-type: decimal !important;
    padding-left: 1.4rem !important;
    margin: 0.65rem 0 !important;
}
.ticket-markdown-prose li {
    margin-bottom: 0.25rem !important;
    color: #374151 !important;
}
.ticket-markdown-prose strong {
    font-weight: 700 !important;
    color: #111827 !important;
}
.ticket-markdown-prose em {
    font-style: italic !important;
}
.ticket-markdown-prose blockquote {
    border-left: 3px solid #f59e0b !important;
    padding: 0.4rem 0.85rem !important;
    color: #4b5563 !important;
    font-style: italic !important;
    background-color: #fffbeb !important;
    border-radius: 0 0.5rem 0.5rem 0 !important;
    margin: 0.65rem 0 !important;
}
.ticket-markdown-prose code {
    background-color: #f3f4f6 !important;
    color: #b45309 !important;
    padding: 0.15rem 0.4rem !important;
    border-radius: 0.35rem !important;
    font-size: 0.85em !important;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    border: 1px solid #e5e7eb !important;
}
.ticket-markdown-prose pre {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    padding: 0.85rem !important;
    border-radius: 0.5rem !important;
    overflow-x: auto !important;
    font-size: 0.85em !important;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    margin: 0.65rem 0 !important;
}
.ticket-markdown-prose pre code {
    background-color: transparent !important;
    color: inherit !important;
    padding: 0 !important;
    border: none !important;
}
.ticket-markdown-prose hr {
    border: 0 !important;
    border-top: 1px solid #e5e7eb !important;
    margin: 1rem 0 !important;
}
</style>
@endonce
