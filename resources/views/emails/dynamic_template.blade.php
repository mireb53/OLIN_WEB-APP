@php
    // Simple variable replacement for {{ var }} style placeholders inside the provided HTML
    $rendered = $html;
    foreach (($vars ?? []) as $k => $v) {
        $rendered = str_replace('{{'.$k.'}}', e((string) $v), $rendered);
        $rendered = str_replace('{{ '.$k.' }}', e((string) $v), $rendered);
    }
@endphp
{!! $rendered !!}
