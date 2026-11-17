@props(['content' => ''])

@php
    $parsed = \Illuminate\Support\Str::markdown($content ?? '', [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'ticket-markdown-prose text-sm text-gray-800 leading-relaxed']) }}>
    {!! $parsed !!}
</div>

@once
@push('styles')
<style>
.ticket-markdown-prose h1, 
.ticket-markdown-prose h2, 
.ticket-markdown-prose h3,
.ticket-markdown-prose h4 {
    font-weight: 800;
    color: #111827;
    margin-top: 0.65rem;
    margin-bottom: 0.3rem;
    border-bottom: 1px solid #f3f4f6;
    padding-bottom: 0.2rem;
}
.ticket-markdown-prose h1 { font-size: 1.05rem; }
.ticket-markdown-prose h2 { font-size: 0.95rem; }
.ticket-markdown-prose h3 { font-size: 0.875rem; }
.ticket-markdown-prose h4 { font-size: 0.825rem; }
.ticket-markdown-prose p {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}
.ticket-markdown-prose p:last-child {
    margin-bottom: 0;
}
.ticket-markdown-prose ul {
    list-style-type: disc;
    padding-left: 1.25rem;
    margin: 0.5rem 0;
}
.ticket-markdown-prose ol {
    list-style-type: decimal;
    padding-left: 1.25rem;
    margin: 0.5rem 0;
}
.ticket-markdown-prose li {
    margin-bottom: 0.2rem;
}
.ticket-markdown-prose strong {
    font-weight: 700;
    color: #1f2937;
}
.ticket-markdown-prose em {
    font-style: italic;
}
.ticket-markdown-prose blockquote {
    border-left: 3px solid #f59e0b;
    padding-left: 0.75rem;
    color: #4b5563;
    font-style: italic;
    background-color: #fffbeb;
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 0.5rem 0;
}
.ticket-markdown-prose code {
    background-color: #f3f4f6;
    color: #b45309;
    padding: 0.12rem 0.35rem;
    border-radius: 0.25rem;
    font-size: 0.82em;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    border: 1px solid #e5e7eb;
}
.ticket-markdown-prose pre {
    background-color: #1e293b;
    color: #f8fafc;
    padding: 0.75rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    font-size: 0.82em;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    margin: 0.5rem 0;
}
.ticket-markdown-prose pre code {
    background-color: transparent;
    color: inherit;
    padding: 0;
    border: none;
}
</style>
@endpush
@endonce
