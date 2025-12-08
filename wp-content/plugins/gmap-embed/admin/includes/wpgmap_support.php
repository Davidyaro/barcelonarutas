<?php if (!defined('ABSPATH')) {
	exit;
} ?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('Support', 'gmap-embed'); ?></h1>
	<?php
	if (!_wgm_is_premium()) {
		echo '<a target="_blank" href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=admin_support&utm_medium=admin_link&utm_campaign=header_menu') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-left:5px;"><i style="line-height: 25px;" class="dashicons dashicons-star-filled"></i> ' . esc_html__('Upgrade ($19 only)', 'gmap-embed') . '</a>';
	}

	echo '<a target="_blank" href="' . esc_url('https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;background-color: #cb5757 !important;color: white !important;"><i style="line-height: 28px;" class="dashicons dashicons-format-chat"></i> ' . esc_html__('LIVE Chat', 'gmap-embed') . '</a>';
	echo '<a href="' . esc_url(admin_url('admin.php?page=wpgmapembed-support')) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;"><i style="line-height: 25px;" class="dashicons  dashicons-editor-help"></i> ' . esc_html__('Documentation', 'gmap-embed') . '</a>';
	?>
	<hr class="wp-header-end">

	<div class="wgm_admin_support_wrapper">

		<div class="wgm_admin_block">
			<header class="wgm_admin_block_header">
				<div class="wgm_admin_block_header_icon">
					<i class="fas fa-file-alt"></i>
				</div>
				<h4 class="wgm_admin_title"><?php esc_html_e('Installation', 'gmap-embed'); ?></h4>
			</header>
			<div class="wgm_admin_block_content">
				<div class="wgm_gmap_instructions">
					<?php
					require_once WGM_PLUGIN_PATH . 'admin/includes/wpgmap_installation_manuals.php';
					?>
				</div>
				<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/installation?utm_source=admin_support&utm_medium=admin_link&utm_campaign=support_installation'); ?>"
					class="wgm_button" target="_blank"><?php esc_html_e('View All', 'gmap-embed'); ?></a>
			</div>
		</div>

		<div class="wgm_admin_block">
			<header class="wgm_admin_block_header">
				<div class="wgm_admin_block_header_icon">
					<i class="fas fa-file-alt"></i>
				</div>
				<h4 class="wgm_admin_title"><?php esc_html_e('How to use', 'gmap-embed'); ?></h4>
			</header>
			<div class="wgm_admin_block_content">
				<div class="wgm_gmap_instructions">
					<?php
					require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_how_to_use_manuals.php';
					?>
				</div>
				<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/customization?utm_source=admin_support&utm_medium=admin_link&utm_campaign=support_how_to_use'); ?>"
					class="wgm_button" target="_blank"><?php esc_html_e('View All', 'gmap-embed'); ?></a>
			</div>
		</div>

		<div class="wgm_admin_block">
			<header class="wgm_admin_block_header">
				<div class="wgm_admin_block_header_icon">
					<i class="fas fa-file-alt"></i>
				</div>
				<h4 class="wgm_admin_title"><?php esc_html_e('Troubleshooting', 'gmap-embed'); ?></h4>
			</header>
			<div class="wgm_admin_block_content">
				<div class="wgm_gmap_instructions">
					<?php
					require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_troubleshooting_manuals.php';
					?>
				</div>
				<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/troubleshooting?utm_source=admin_support&utm_medium=admin_link&utm_campaign=support_troubleshooting'); ?>"
					class="wgm_button" target="_blank"><?php esc_html_e('View All', 'gmap-embed'); ?></a>
			</div>
		</div>

	</div>

	<div class="wgm_admin_support_wrapper" style="margin-top: 50px;">
		<div class="wgm_admin_block">
			<header class="wgm_admin_block_header">
				<div class="wgm_admin_block_header_icon">
					<i class="fas fa-user-plus"></i>
				</div>
				<h4 class="wgm_admin_title"><?php esc_html_e('Contribute to WP Google Map', 'gmap-embed'); ?></h4>
			</header>
			<div class="wgm_admin_block_content">
				<p><?php esc_html_e('You can contribute to make WP Google Map better reporting bugs, creating issues at', 'gmap-embed'); ?>
					<a href="<?php echo esc_url('https://github.com/milonfci/gmap-embed-lite/issues/new'); ?>"
						target="_blank">Github.</a>
					<?php esc_html_e('We are looking forward for your feedback.', 'gmap-embed'); ?>
				</p>
				<a href="https://github.com/milonfci/gmap-embed-lite/issues/new" class="wgm_button"
					target="_blank"><?php esc_html_e('Report an issue', 'gmap-embed'); ?></a>
			</div>
		</div>
		<div class="wgm_admin_block">
			<header class="wgm_admin_block_header">
				<div class="wgm_admin_block_header_icon">
					<i class="fas fa-headset"></i>
				</div>
				<h4 class="wgm_admin_title"><?php esc_html_e('Need Help?', 'gmap-embed'); ?></h4>
			</header>
			<div class="wgm_admin_block_content">

				<p><?php esc_html_e('Stuck with something? Get help from the community on', 'gmap-embed'); ?> <a
						href="<?php echo esc_url('https://wordpress.org/support/plugin/gmap-embed/#new-topic-0'); ?>"
						target="_blank"><?php esc_html_e('WordPress.org Forum', 'gmap-embed'); ?></a>
					<?php esc_html_e('or', 'gmap-embed'); ?> <a
						href="<?php echo esc_url('https://www.facebook.com/Google-Map-SRM-100856491527309'); ?>"
						target="_blank"><?php esc_html_e('Facebook Community', 'gmap-embed'); ?></a>.
					<?php esc_html_e('In case of emergency, initiate a live chat at', 'gmap-embed'); ?> <a
						href="<?php echo esc_url('https://wpgooglemap.com?utm_source=admin_support&utm_medium=admin_link&utm_campaign=support_need_help'); ?>"
						target="_blank"><?php esc_html_e('WP Google Map website', 'gmap-embed'); ?></a>.
				</p>
				<a href="<?php echo esc_url('https://wpgooglemap.com/contact-us?utm_source=admin_support&utm_medium=admin_link&utm_campaign=support_need_help'); ?>"
					class="wgm_button" target="_blank"><?php esc_html_e('Get Support', 'gmap-embed'); ?></a>

			</div>
		</div>

		<div class="wgm_admin_block">
			<header class="wgm_admin_block_header">
				<div class="wgm_admin_block_header_icon">
					<i class="far fa-heart"></i>
				</div>
				<h4 class="wgm_admin_title"><?php esc_html_e('Show your Love', 'gmap-embed'); ?></h4>
			</header>
			<div class="wgm_admin_block_content">
				<p><?php esc_html_e('We love to have you in WP Google Map family. We are making it more awesome everyday. Take your 2 minutes to review the plugin and spread the love to encourage us to keep it going.', 'gmap-embed'); ?>
				</p>

				<a href="<?php echo esc_url('https://wordpress.org/support/plugin/gmap-embed/reviews/?filter=5#new-post'); ?>"
					class="review-flexia wgm_button"
					target="_blank"><?php esc_html_e('Leave a Review', 'gmap-embed'); ?></a>
			</div>
		</div>
	</div>
	<div class="wgm_admin_support_wrapper" style="margin-top: 50px;">
		<iframe width="1904" height="768" src="<?php echo esc_url('https://www.youtube.com/embed/ErRy5lqTPjY'); ?>"
			title="<?php esc_attr_e('YouTube video player', 'gmap-embed'); ?>" frameborder="0"
			allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
			allowfullscreen></iframe>
	</div>
</div>