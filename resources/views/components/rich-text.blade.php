{{--
    Renders already-sanitized rich text (e.g. $listing->summaryHtml()) with
    readable list / heading / table / link styles that Tailwind's reset
    would otherwise strip. Works on both the backend and the public frontend.

    Usage: <x-rich-text :html="$listing->summaryHtml()" class="text-sm text-gray-700" />
--}}
@props(['html'])

@once
<style>
  .rich-text > * + * { margin-top: .6em; }
  .rich-text ul { list-style: disc; padding-left: 1.4em; }
  .rich-text ol { list-style: decimal; padding-left: 1.4em; }
  .rich-text li + li { margin-top: .25em; }
  .rich-text h1, .rich-text h2 { font-size: 1.15em; font-weight: 700; color: #111827; }
  .rich-text h3, .rich-text h4, .rich-text h5, .rich-text h6 { font-size: 1.05em; font-weight: 600; color: #111827; }
  .rich-text a { color: #059669; text-decoration: underline; }
  .rich-text blockquote { border-left: 3px solid #e5e7eb; padding-left: .9em; color: #4b5563; }
  .rich-text table { width: 100%; border-collapse: collapse; font-size: .95em; }
  .rich-text th, .rich-text td { border: 1px solid #e5e7eb; padding: .35em .6em; text-align: left; }
  .rich-text th { background: #f9fafb; font-weight: 600; }
  .rich-text hr { border-color: #e5e7eb; }
</style>
@endonce

<div {{ $attributes->merge(['class' => 'rich-text leading-relaxed break-words']) }}>{!! $html !!}</div>
