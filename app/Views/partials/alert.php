<?php
/**
 * Partial: Alert
 *
 * Usage:
 *   <?= partial('partials/alert', ['type' => 'success', 'message' => 'Saved!']) ?>
 *
 * Variables:
 *   $type    — 'success' | 'error' | 'warning' | 'info'  (default: 'info')
 *   $message — The alert text.
 *   $title   — Optional bold title shown before the message.
 *
 * Also automatically renders session flash messages:
 *   Session::flash('success', 'Changes saved!');
 *   Session::flash('error',   'Something went wrong.');
 */

use App\Core\Session;

$type    ??= 'info';
$message ??= Session::getFlash($type, null);
$title   ??= null;

if ($message === null) {
    return; // Nothing to show
}

$icons = [
    'success' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>',
    'error'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    'warning' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'info'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
];

$icon = $icons[$type] ?? $icons['info'];
?>
<div class="alert alert-<?= e($type) ?>" role="alert">
    <div style="display:flex;align-items:flex-start;gap:.625rem;">
        <span style="flex-shrink:0;margin-top:.1rem"><?= raw($icon) ?></span>
        <div>
            <?php if ($title !== null): ?>
                <strong><?= e($title) ?></strong>
            <?php endif; ?>
            <?= e($message) ?>
        </div>
    </div>
</div>
