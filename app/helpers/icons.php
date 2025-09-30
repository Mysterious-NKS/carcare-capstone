<?php
// app/helpers/icons.php
if (!function_exists('icon')) {
    /**
     * Minimal, consistent inline icon helper.
     * Usage: <?= icon('bell','h-5 w-5') ?>
     */
    function icon(string $name, string $class = 'h-5 w-5'): string
    {
        $map = [
            'check'    => '<path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
            'bell'     => '<path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2z"/><path d="M20 16V11A8 8 0 1 0 4 11v5l-2 2v1h20v-1l-2-2z"/>',
            'user'     => '<path d="M12 14c3.31 0 6 2.69 6 6H6c0-3.31 2.69-6 6-6z"/><circle cx="12" cy="7" r="4"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'alert'    => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'tasks'    => '<path d="M9 6h11M9 12h11M9 18h11"/><path d="m5 6 1 1 2-2M5 12l1 1 2-2M5 18l1 1 2-2" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
            'chat'     => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />',
            'wrench'   => '<path d="M22 2l-6 6M11 7l-9 9v5h5l9-9"/><circle cx="18" cy="6" r="3"/>',
            // small extras for future consistency
            'send'     => '<path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 2 9 22 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            'plus'     => '<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>',
            'arrow'    => '<path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>',
        ];
        $d = $map[$name] ?? '';
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="' .
               htmlspecialchars($class, ENT_QUOTES) .
               '" fill="currentColor" aria-hidden="true">'.$d.'</svg>';
    }
}
