<?php
if (isset($_GET['message']) && isset($_GET['wgm_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_settings_nonce'])), 'wgm_settings') && !isset($_GET['settings-updated'])) {
	?>
	<div class="message">
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p>
				<strong>
					<?php
					$allowed_html = [
						'a' => [
							'class' => [],
							'id' => [],
							'title' => [],
							'href' => [],
						],
						'i' => [
							'style' => []
						],
						'span' => [
							'class' => [],
							'style' => []
						],

					];
					$message_status = sanitize_text_field(wp_unslash($_GET['message']));
					switch ($message_status) {
						case 1:
							// translators: %s: YouTube tutorial URL.
							echo wp_kses_post(
								sprintf(
									// translators: %s: YouTube tutorial URL.
									__('Map has been created Successfully. <a href="%s" target="_blank"> See How to use &gt;&gt;</a>', 'gmap-embed'),
									esc_url('https://youtu.be/ErRy5lqTPjY?t=255')
								),
								$allowed_html
							);
							break;
						case 3:
							// translators: %s: Add New menu admin URL.
							echo wp_kses(
								// translators: %s: Add New menu admin URL.
								sprintf(
									// translators: %s: Add New menu admin URL.
									__('API key updated Successfully, Please click on <a href="%s"><i style="color: green;">Add New</i></a> menu to add new map.', 'gmap-embed'),
									esc_url(admin_url('admin.php?page=wpgmapembed-new'))
								),
								$allowed_html
							);
							break;
						case 4:
							echo wp_kses($message, $allowed_html);
							break;
						case -1:
							echo esc_html__('Map Deleted Successfully.', 'gmap-embed');
							break;
					}
					?>
				</strong>
			</p>
			<button type="button" class="notice-dismiss"><span
					class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'gmap-embed'); ?></span>
			</button>
		</div>
	</div>
	<?php
}
?>