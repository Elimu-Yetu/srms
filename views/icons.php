<?php
/** Inline SVG icons — no icon font, no CDN, works on a disconnected LAN. */
function icon(string $name, int $size = 18): string
{
    static $p = null;
    if ($p === null) $p = [
        'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'users'     => '<path d="M16 20v-1.5A3.5 3.5 0 0 0 12.5 15h-5A3.5 3.5 0 0 0 4 18.5V20"/><circle cx="10" cy="8" r="3.4"/><path d="M20 20v-1.4a3.5 3.5 0 0 0-2.6-3.4M15.5 5.2a3.4 3.4 0 0 1 0 6.1"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'layers'    => '<path d="M12 3 3 7.5l9 4.5 9-4.5L12 3Z"/><path d="M3 12.5 12 17l9-4.5M3 17 12 21.5 21 17"/>',
        'book'      => '<path d="M4 5.5A2 2 0 0 1 6 3.5h13v15H6a2 2 0 0 0-2 2Z"/><path d="M4 5.5v15"/>',
        'check'     => '<path d="M20 6.5 9.5 17 4.5 12"/>',
        'list'      => '<path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
        'calendar'  => '<rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17"/>',
        'calendar-check' => '<rect x="3.5" y="5" width="17" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17M9 15l2 2 4-4"/>',
        'file'      => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/>',
        'chart'     => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'bell'      => '<path d="M18 15V10a6 6 0 1 0-12 0v5l-1.5 3h15Z"/><path d="M10 21h4"/>',
        'card'      => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6 14.5h4"/>',
        'award'     => '<circle cx="12" cy="9" r="5.5"/><path d="M8.5 13.5 7 21l5-2.6L17 21l-1.5-7.5"/>',
        'printer'   => '<path d="M7 8V3h10v5"/><rect x="3.5" y="8" width="17" height="8" rx="2"/><path d="M7 16h10v5H7z"/>',
        'shield'    => '<path d="M12 3 5 6v6c0 4.2 3 7.7 7 9 4-1.3 7-4.8 7-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'cog'       => '<circle cx="12" cy="12" r="3"/><path d="M12 2.5v3M12 18.5v3M4.2 7l2.6 1.5M17.2 15.5l2.6 1.5M4.2 17l2.6-1.5M17.2 8.5 19.8 7"/>',
        'eye'       => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
        'database'  => '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
        'kitchen'   => '<path d="M4 8h12v5a6 6 0 0 1-12 0Z"/><path d="M16 9h2.5a2.5 2.5 0 0 1 0 5H16"/><path d="M3 21h14"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'logout'    => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 8 6 12l4 4M6 12h9"/>',
        'search'    => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'back'      => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'download'  => '<path d="M12 3v12M7 11l5 5 5-5M4 20h16"/>',
        'edit'      => '<path d="M4 20h4L20 8l-4-4L4 16Z"/><path d="m14.5 5.5 4 4"/>',
        'x'         => '<path d="M6 6l12 12M18 6 6 18"/>',
        'alert'     => '<path d="M12 3 2.5 20h19Z"/><path d="M12 9v5M12 17h.01"/>',
        'clock'     => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'inbox'     => '<path d="M3.5 12.5 6 5h12l2.5 7.5v6a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2Z"/><path d="M3.5 12.5H8l1 2.5h6l1-2.5h4.5"/>',
        'star'      => '<path d="m12 4 2.5 5.2 5.5.8-4 3.9 1 5.6L12 16.9 7 19.5l1-5.6-4-3.9 5.5-.8Z"/>',
    ];
    $d = $p[$name] ?? $p['grid'];
    return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" '
         . 'stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}
