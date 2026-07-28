=== Soovex WebP Converter – Convert Images | Optimize & Compress | Unlimited Conversions ===
Contributors: imustafiur
Tags: webp, image optimization, converter, performance, seo
Requires at least: 6.4
Tested up to: 7.0.2
Stable tag: 1.0.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://soovex.com/

Convert WordPress images to WebP automatically. Optimize page speed, boost SEO, and reduce bandwidth with unlimited conversions and smart backups.

== Description ==

Soovex WebP Converter is a powerful, feature-rich WordPress plugin that automatically converts your media library images (JPG, PNG) to modern WebP format. This image optimization plugin significantly reduces file sizes by 25-35% while maintaining excellent visual quality, resulting in faster page load times, improved Google PageSpeed scores, better SEO rankings, and reduced bandwidth usage.

**Why Use WebP Image Format?**
WebP is a modern image format developed by Google that provides superior compression compared to traditional JPEG and PNG formats. It offers both lossless and lossy compression, allowing you to achieve smaller file sizes without sacrificing image quality. WebP images load faster, consume less bandwidth, improve Core Web Vitals scores, and enhance user experience - especially on mobile devices. All modern browsers support WebP, and this plugin automatically serves WebP to compatible browsers while falling back to original formats for older browsers.

**Key Features:**

* **Unlimited Image Conversions**: Convert unlimited JPG/PNG images to WebP format with no restrictions
* **One-Click Conversion**: Convert single, multiple, or all images with simple one-click operations
* **Smart Backup System**: Automatically creates secure backups of original images before conversion
* **Media Library Integration**: Seamlessly convert images directly from WordPress media library
* **Bulk Processing**: Convert single, multiple, or all images at once with background processing
* **URL Conversion**: Convert images from external URLs and save to media library
* **Quality Control**: Adjustable WebP compression quality (40-100) for optimal balance
* **Auto-Convert Uploads**: Automatically convert new image uploads to WebP format
* **Revert Functionality**: Restore original images from backups anytime with one click
* **Activity Logging**: Comprehensive activity log tracking all conversions, failures, and reversions
* **Real-Time Statistics**: Monitor conversion stats, storage savings, and performance metrics
* **Modern Dashboard**: Beautiful, intuitive dashboard with conversion statistics and quick actions
* **Lazy Loading Support**: Optional native lazy loading for improved page performance
* **Browser Compatibility**: Automatic fallback to original images for unsupported browsers
* **Server Health Check**: Built-in server requirements verification and status monitoring
* **Backup Management**: Flexible backup retention settings with automatic cleanup options
* **SEO Optimization**: Improves Google PageSpeed scores and Core Web Vitals
* **Performance Monitoring**: Track storage savings, conversion statistics, and bandwidth reduction

== Installation ==

1. **Upload Plugin:** Upload the plugin files to the `/wp-content/plugins/soovex-webp-converter/` directory, or install the plugin directly through the WordPress admin plugins screen by searching for "Soovex WebP Converter".
2. **Activate Plugin:** Activate the plugin through the 'Plugins' screen in your WordPress admin dashboard.
3. **Access Dashboard:** Navigate to **Soovex WebP Converter** in your WordPress admin menu to access the plugin dashboard.
4. **Configure Settings:** Configure your settings including backup options, compression quality (recommended: 80-85%), auto-convert features, and lazy loading preferences.
5. **Start Converting:** Begin converting your images using the dashboard conversion options or directly from the WordPress media library.

**Quick Start:** After activation, you can immediately start converting images. We recommend enabling backups and setting compression quality to 80-85% for optimal results.

== Documentation ==

= Conversion Options =

**Convert Single Image**
Convert one image at a time from your media library to WebP format.

* Click the "Convert Single Image" button on the Dashboard
* Select an image from your media library
* Click "Convert" to start the conversion
* The converted WebP image will be saved automatically

**Note:** Only JPEG and PNG images can be converted to WebP format.

**Convert Multiple Images**
Convert multiple images at once from your media library.

* Click the "Convert Multiple Images" button on the Dashboard
* Click "Select Images" to open the media library
* Hold Ctrl (Windows/Linux) or Cmd (Mac) while clicking to select multiple images
* Click "Use these images" to confirm your selection
* Click "Convert" to start batch conversion
* Monitor progress in the progress modal

**Tip:** Batch conversion processes images in the background, so you can continue working while conversions complete.

**Convert by URL**
Convert an image from an external URL to WebP format.

* Click the "Convert by URL" button on the Dashboard
* Enter the full URL of the image (must be a direct link to a JPEG or PNG image)
* Click "Convert" to download and convert the image
* The converted image will be saved to your media library

**Important:** The URL must be publicly accessible and point directly to an image file (not an HTML page).

**Convert All Media**
Convert all JPEG and PNG images in your media library to WebP format at once.

* Click the "Convert All Media" button on the Dashboard
* Confirm the action in the modal dialog
* Monitor the progress in the progress modal
* The conversion will process all eligible images in your media library

**Warning:** This action will convert all images. Make sure you have backups enabled if you want to keep originals.

= Compression Settings =

**Image Compression Quality**
Control the balance between image quality and file size.

**Quality Levels:**
* **40-60%:** High compression, smaller file size, noticeable quality loss
* **60-80%:** Balanced compression, good quality with reasonable file size (recommended)
* **80-100%:** Low compression, larger file size, minimal quality loss

**Recommended:** 80-85% provides an excellent balance between quality and file size for most websites.

= Backup & Recovery =

**Enable Data Backup**
Keep original images as backup after conversion. This allows you to revert conversions if needed.

**Benefits:**
* Original images are preserved in a backup folder
* You can revert any conversion at any time
* Provides safety net for all conversions

**Note:** Backups use additional storage space. Monitor your disk usage if you have many images.

**Backup Reminder**
Get notified before backup data is automatically deleted.

When enabled, you'll receive admin notices 3 days before backups are scheduled for deletion, giving you time to take action if needed.

**Automatically Delete Original Data from Backup**
Set how long to keep backup files before automatic deletion.

**Options:**
* **7-365 days:** Keep backups for a specific duration
* **Custom:** Set your own duration (1-3650 days)
* **Never:** Keep backups permanently (uses more storage)

**Critical:** Once deleted, original images cannot be recovered. Ensure you have backups elsewhere if needed.

**All Data Recovery (Revert All)**
Revert all converted images back to their original format.

This action will restore all converted WebP images to their original JPEG/PNG format using the backup files. Only works if backups are enabled and available.

= Auto Features =

**Auto-convert New Uploads**
Automatically convert images to WebP when they are uploaded to the media library.

**How it works:**
* When enabled, all new JPEG/PNG uploads are automatically converted
* Conversion happens in the background after upload
* Original images are kept as backup (if backup is enabled)
* No manual intervention required

**Requirement:** Auto-convert requires backup to be enabled. If backup is disabled, auto-convert will also be disabled.

**Enable Lazy Loading**
Improve page load times by loading images only when they enter the viewport.

**Benefits:**
* Faster initial page load times
* Reduced bandwidth usage
* Improved performance on mobile devices
* Better user experience on image-heavy pages

Lazy loading adds the native "loading='lazy'" attribute to all img tags. Modern browsers support this natively without additional JavaScript.

**Serve WebP Images**
Automatically serve WebP images to browsers that support them for better performance.

**How it works:**
* Browsers that support WebP receive the optimized WebP version
* Older browsers automatically receive the original format
* No changes needed to your theme or content
* Works transparently in the background

**Compatibility:** All modern browsers support WebP. Older browsers will automatically fall back to original formats.

= Activity Log =

**Viewing Activity Log**
Track all conversion activities, including successful conversions, failures, and reversions.

**Information displayed:**
* **Original Image:** Name of the source image
* **WebP Image:** Name of the converted WebP file
* **Status:** Conversion status (Converted, Failed, Reverted)
* **Date:** When the action occurred

**Revert Single Image**
Revert a single converted image back to its original format.

Click the "Revert" button next to any converted image in the Activity Log to restore it to its original format. Only available for successfully converted images with backups.

**Retry Failed Conversion**
Retry a conversion that previously failed.

Click the "Retry" button next to any failed conversion in the Activity Log to attempt the conversion again. Useful if the failure was due to temporary issues.

**Clear Logs**
Clear all activity log entries.

Use the "Clear Logs" button to remove all activity log entries. This action cannot be undone but does not affect your images.

= Advanced Options =

**Reset Everything**
Reset all plugin settings and data to default values.

**This action will:**
* Revert all converted images to original format
* Clear all activity logs
* Reset all settings to default
* Delete all backup files

**⚠️ This action cannot be undone!**

= Tips & Best Practices =

**Recommended Settings:**
* Enable backups for safety
* Set compression quality to 80-85%
* Enable auto-convert for new uploads
* Enable lazy loading for better performance

**Performance Tips:**
* Use batch conversion for many images
* Monitor disk space with backups enabled
* Check Activity Log regularly
* Test compression quality on sample images first

= Server Requirements =

**Server Health Check**
The plugin includes a built-in server health check feature that verifies:

* **GD Extension:** Must be enabled for image processing
* **WebP Support:** GD extension must support WebP format
* **Memory Limit:** Recommended minimum of 128MB

**Note:** If WebP support is not available, contact your hosting provider to enable GD extension with WebP support.

== Frequently Asked Questions ==

= What is WebP and why should I use it? =

WebP is a modern image format developed by Google that provides superior lossless and lossy compression for images on the web. It can reduce file sizes by 25-35% compared to JPEG and PNG while maintaining the same visual quality, resulting in faster page loads, improved Google PageSpeed scores, and better SEO rankings. WebP images load faster, consume less bandwidth, and improve user experience, especially on mobile devices.

= Does this plugin work with all WordPress themes? =

Yes, this plugin works seamlessly with all WordPress themes, including popular page builders like Elementor, Gutenberg, Divi, and Beaver Builder. It converts images at the server level and automatically serves WebP versions to supported browsers while falling back to original images for unsupported browsers. No theme modifications, code changes, or template edits are required.

= Will this plugin affect or delete my original images? =

No, your original images are safely backed up before conversion by default. The plugin creates automatic backups of all original JPEG and PNG images before converting them to WebP format. You can revert to the original images at any time using the "Revert" option in the Activity Log or "Revert All" in Settings. You can disable backups in settings if you prefer, but this is not recommended.

= Can I choose which images to convert? =

Yes, you have complete control over which images to convert. The plugin offers multiple conversion options:
* **Single Image:** Convert one image at a time from the media library
* **Multiple Images:** Select and convert multiple images in bulk
* **All Images:** Convert all JPEG and PNG images in your media library at once
* **URL Conversion:** Convert images from external URLs
* **Auto-Convert:** Automatically convert new uploads when enabled

= What happens if I deactivate or uninstall the plugin? =

If you deactivate the plugin, your converted WebP images will remain as files, but the automatic serving will stop. Original images are safely stored in backups and can be restored if needed. The plugin can be reactivated anytime without losing your converted images or settings. If you uninstall the plugin, you can choose to keep or remove converted images and backups.

= Is there a limit on how many images I can convert? =

No, there are absolutely no limits. You can convert unlimited images with this plugin, making it perfect for websites with large media libraries, e-commerce stores, photography sites, and content-heavy blogs. The plugin handles batch conversions efficiently in the background.

= Will converting images to WebP improve my website's SEO? =

Yes, absolutely! Converting images to WebP format significantly improves SEO in multiple ways:
* **Faster Page Load Times:** Smaller file sizes result in faster loading, which is a key Google ranking factor
* **Better PageSpeed Scores:** Improves Google PageSpeed Insights scores for both mobile and desktop
* **Reduced Bounce Rate:** Faster loading improves user experience and reduces bounce rates
* **Mobile Optimization:** Better performance on mobile devices, which Google prioritizes
* **Bandwidth Savings:** Reduces server bandwidth usage and hosting costs

The plugin maintains all image metadata, alt text, and SEO attributes during conversion.

= How do I adjust the WebP compression quality? =

You can easily adjust the WebP compression quality from 40 to 100 in the plugin settings or dashboard. Higher values mean better quality but larger file sizes, while lower values mean smaller files but potentially lower quality.

**Recommended Quality Settings:**
* **40-60%:** High compression, smaller file size, noticeable quality loss (use for thumbnails or low-priority images)
* **60-80%:** Balanced compression, good quality with reasonable file size (recommended for most websites)
* **80-100%:** Low compression, larger file size, minimal quality loss (use for high-quality photography or featured images)

For most websites, 80-85% provides an excellent balance between quality and file size, offering significant compression without visible quality loss.

= What browsers support WebP format? =

WebP is supported by all modern browsers including Chrome, Firefox, Safari (iOS 14+), Edge, Opera, and mobile browsers. The plugin automatically detects browser support and serves WebP versions to compatible browsers while automatically falling back to original JPEG/PNG formats for older browsers. This ensures compatibility with 99%+ of all web browsers.

= How much storage space and bandwidth will I save? =

On average, you can expect to save 25-35% in storage space and bandwidth usage. The plugin tracks your savings and displays them in the dashboard statistics. For example, if you have 1GB of images, converting to WebP typically reduces storage to 650-750MB, saving 250-350MB. The plugin shows real-time statistics including total images converted, storage saved, and conversion success rate.

= How do I revert converted images back to original format? =

You can revert converted images in multiple ways:
* **Single Image Revert:** Use the "Revert" button in the Activity Log next to any converted image
* **Bulk Revert:** Use the "Revert All" option in the Settings panel to restore all converted images
* **Media Library:** Revert individual images directly from the WordPress media library

**Important:** Reverting requires backups to be enabled. If backups are disabled or deleted, you cannot revert images. Always keep backups enabled for safety.

= How does the backup system work? =

The plugin automatically creates backups of all original images before conversion. Backups are stored in a separate secure folder and can be managed through settings:
* **Automatic Backup:** Enabled by default to protect your original images
* **Backup Duration:** Set how long to keep backups (7-365 days, custom 1-3650 days, or never)
* **Backup Reminders:** Get notified 3 days before backups are scheduled for deletion
* **Storage Management:** Monitor backup storage usage in the dashboard

**Warning:** Once backups are deleted, original images cannot be recovered. Ensure you have external backups if needed.

= Can I convert images from external URLs or other websites? =

Yes, you can convert images from external URLs using the "Convert by URL" feature:
* Enter the full URL of the image (must be a direct link to a JPEG or PNG image file)
* The image will be downloaded and automatically converted to WebP format
* The converted image will be saved to your WordPress media library

**Requirements:** The URL must be publicly accessible and point directly to an image file (not an HTML page or protected content). The image must be in JPEG or PNG format.

= Does the plugin support lazy loading for images? =

Yes, the plugin includes optional native lazy loading support:
* Enable lazy loading in the Settings panel
* Automatically adds native "loading='lazy'" attribute to all img tags
* Improves initial page load times by loading images only when they enter the viewport
* Works with all modern browsers without additional JavaScript
* Reduces bandwidth usage and improves Core Web Vitals scores

Lazy loading is especially beneficial for image-heavy pages, blogs, galleries, and e-commerce product pages.

= What are the server requirements for this plugin? =

The plugin requires:
* **WordPress:** 6.4 or higher
* **PHP:** 7.4 or higher (PHP 8.0+ recommended for better performance)
* **GD Extension:** Must be enabled for image processing
* **WebP Support:** GD extension must support WebP format (included in PHP 7.2+)
* **Memory:** Recommended minimum of 128MB PHP memory limit (256MB+ for large images)
* **Disk Space:** Sufficient space for backups (if enabled)

The plugin includes a built-in Server Health Check feature that automatically verifies these requirements and displays server status in the dashboard.

= How does auto-convert work for new image uploads? =

When auto-convert is enabled, all new JPEG and PNG images uploaded to your WordPress media library are automatically converted to WebP format in the background:
* Conversion happens automatically after upload
* Original images are kept as backup (if backup is enabled)
* No manual intervention required
* Works with all upload methods (media library, drag-and-drop, bulk upload, etc.)

**Note:** Auto-convert requires backup to be enabled. If backup is disabled, auto-convert will also be disabled for safety.

= Can I use this plugin with caching plugins? =

Yes, this plugin works perfectly with all popular WordPress caching plugins including WP Rocket, W3 Total Cache, WP Super Cache, LiteSpeed Cache, and others. The plugin serves WebP images at the server level, which works seamlessly with caching. You may need to clear your cache after converting images to see the WebP versions served to visitors.

= Does the plugin work with CDN services? =

Yes, the plugin works with CDN services like Cloudflare, MaxCDN, KeyCDN, and others. WebP images are served from your server, and CDN services will cache and deliver them to visitors. Some CDN services also offer automatic WebP conversion, but using this plugin gives you more control and better integration with WordPress.

= How do I monitor conversion progress and statistics? =

The plugin provides comprehensive monitoring tools:
* **Dashboard Statistics:** Real-time stats showing total images converted, storage saved, and success rate
* **Activity Log:** Detailed log of all conversion activities with timestamps, file names, and status
* **Progress Tracking:** Visual progress bars for bulk conversions
* **Server Health:** Monitor server requirements and WebP support status

All statistics are displayed in the plugin dashboard and can be viewed anytime.

= What image formats can be converted to WebP? =

The plugin converts JPEG (JPG) and PNG images to WebP format. GIF, SVG, and other formats are not converted as they serve different purposes. JPEG images are best for photographs, while PNG images are ideal for graphics with transparency. Both formats convert excellently to WebP with significant file size reduction.

= Is this plugin compatible with WooCommerce? =

Yes, this plugin is fully compatible with WooCommerce and automatically converts product images, gallery images, and thumbnails. The plugin works seamlessly with WooCommerce product pages, category pages, and shop archives. Converting product images to WebP can significantly improve WooCommerce store performance and page load times.

= Can I convert images in bulk or batches? =

Yes, the plugin offers powerful bulk conversion features:
* **Multiple Images:** Select and convert multiple images at once
* **Convert All:** Convert all eligible images in your media library
* **Background Processing:** Bulk conversions run in the background so you can continue working
* **Progress Tracking:** Monitor conversion progress with real-time updates
* **Batch Management:** Handle large media libraries efficiently

Batch conversion is perfect for optimizing existing websites with hundreds or thousands of images.

= What happens if a conversion fails? =

If a conversion fails, the plugin logs the error in the Activity Log with details about what went wrong. You can:
* View failed conversions in the Activity Log
* Retry failed conversions with the "Retry" button
* Check server requirements using the Server Health Check
* Contact support if issues persist

Common causes of failures include insufficient server memory, large file sizes, or server configuration issues.

= How do I reset all plugin settings? =

You can reset all plugin settings and data using the "Reset Everything" option in the Settings panel. This will:
* Revert all converted images to original format
* Clear all activity logs
* Reset all settings to default values
* Delete all backup files

**Warning:** This action cannot be undone. Use with caution and ensure you have external backups if needed.

== Screenshots ==

1. **Dashboard Overview** - Modern dashboard showing conversion statistics, storage savings, and quick action buttons
2. **Media Library Integration** - WebP status column and conversion actions directly in WordPress media library
3. **Activity Log** - Detailed log of all conversion activities with timestamps and file information
4. **Settings Panel** - Comprehensive settings for backup management, compression quality, and feature toggles
5. **Bulk Conversion** - Interface for converting multiple images or all images at once
6. **URL Conversion** - Tool for converting images by providing their URLs

== Changelog ==

= 1.0.3 =
* Tested up to WordPress 7.0.2

= 1.0.2 =
* Added comprehensive Help page with detailed documentation for all features
* Improved multiple image selection with clear visual instructions
* Enhanced plugin menu structure and navigation
* Fixed JavaScript errors and improved dashboard functionality
* Improved activity log loading and display
* Added better user guidance for all conversion options
* Enhanced UI with visual instructions and tooltips
* Fixed compatibility issues and improved code quality

= 1.0.1 =
* Added dedicated Settings page with comprehensive configuration options
* Added Server Health Check feature with detailed server information
* Improved dashboard UI with better organization
* Enhanced settings management and user experience
* Fixed minor bugs and improved performance
* Code cleanup and optimization

= 1.0.0 =
* Initial release
* Convert JPG/PNG images to WebP format
* Automatic backup system for original images
* Media library integration with status indicators
* Bulk conversion capabilities
* URL-based image conversion
* Adjustable compression quality
* Activity logging system
* Dashboard with statistics and monitoring
* Auto-convert new uploads option
* Lazy loading support
* Browser compatibility with automatic fallback
* Comprehensive settings panel
* Backup management and cleanup tools
* Performance monitoring and storage tracking

== Upgrade Notice ==

= 1.0.0 =
* Initial release of Soovex WebP Converter. Start optimizing your images today!