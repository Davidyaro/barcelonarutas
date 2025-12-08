<ul class="wgm_gmap_embed_marker_icons">
	<?php
	global $wpdb;
	$icon_fetch = false;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpgmap_marker_icons = $wpdb->get_results("SELECT type, file_name FROM {$wpdb->prefix}wgm_icons", OBJECT);
	if (count($wpgmap_marker_icons) > 0) {
		foreach ($wpgmap_marker_icons as $key => $marker_icon) {
			$icon_fetch = true;
			$image_path = $marker_icon->file_name;
			if ($marker_icon->type === 'pre_uploaded_icon') {
				$image_path = plugin_dir_url(__FILE__) . '../assets/images/markers/' . basename($marker_icon->file_name);
			}
			?>
			<li style="display: inline;
	padding: 2px;
	margin: 5px;
	border: 1px gray solid;
	width: 40px;
	height: 40px;
	float: left;
	text-align: center;
	cursor: pointer;overflow: hidden">
				<img width="32" height="32" src="<?php echo esc_url($image_path); ?>"
					onclick="wpgmapChangeCurrentMarkerIcon(this);" alt="<?php echo esc_attr(basename($image_path)); ?>"
					title="<?php echo esc_attr(basename($image_path)); ?>" style="width:32px;height:auto;" />
			</li>
			<?php
		}
	}
	// Load default icons for a directory
	$dir = WGM_ICONS_DIR;
	$file_display = array('jpg', 'jpeg', 'png', 'gif');

	if (file_exists($dir) == false) {
		echo 'Directory \'', esc_html($dir), '\' not found!';

	} else {
		$icon_fetch = true;
		$dir_contents = scandir($dir);
		foreach ($dir_contents as $file) {
			$image_data = explode('.', $file);
			$file_type = strtolower(end($image_data));
			if ('.' !== $file && '..' !== $file && true == in_array($file_type, $file_display)) {
				?>
				<li style="display: inline;
	padding: 2px;
	margin: 5px;
	border: 1px gray solid;
	width: 40px;
	height: 40px;
	float: left;
	text-align: center;
	cursor: pointer;">
					<img onclick="wpgmapChangeCurrentMarkerIcon(this);" alt="<?php echo esc_attr($image_data[0]); ?>"
						title="<?php echo esc_attr($image_data[0]); ?>" src="<?php echo esc_url(WGM_ICONS . $file); ?>" />
				</li>
				<?php
			}
		}
	}


	//------------------end---------------
	if (!$icon_fetch) {
		echo esc_html__('No icon found, please upload icon by clicking the Upload Icon button', 'gmap-embed');
	}
	?>
</ul>