<?php
/**
 * Activity log row template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get attachment ID from the log
$attachment_id = intval($log->attachment_id);

$status_class = '';
switch ($log->status) {
    case 'Converted':
        $status_class = 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300';
        break;
    case 'Reverted':
        $status_class = 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300';
        break;
    case 'Failed':
        $status_class = 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300';
        break;
    default:
        $status_class = 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300';
}
?>
<tr>
    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium"><?php echo esc_html($log->original_image); ?></td>
    <td class="whitespace-nowrap px-6 py-4 text-sm"><?php echo esc_html($log->webp_image); ?></td>
    <td class="whitespace-nowrap px-6 py-4 text-sm">
        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo esc_attr($status_class); ?>">
            <?php echo esc_html($log->status); ?>
        </span>
    </td>
    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($log->date))); ?></td>
    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
        <?php if ($log->status === 'Converted' && $attachment_id) : ?>
            <button class="webp-cp-revert-image text-primary hover:text-primary/80" data-log-id="<?php echo esc_attr($log->id); ?>" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">Revert</button>
        <?php elseif ($log->status === 'Failed' && $attachment_id) : ?>
            <button class="webp-cp-retry-image text-primary hover:text-primary/80" data-log-id="<?php echo esc_attr($log->id); ?>" data-attachment-id="<?php echo esc_attr($attachment_id); ?>">Retry</button>
        <?php else : ?>
            <span class="text-gray-400 dark:text-gray-500">-</span>
        <?php endif; ?>
    </td>
</tr>