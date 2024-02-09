<?php
// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

// Init plugin options to white list the options
function svgAvatars_admin_init(){
	register_setting( "svgAvatars_plugin_settings", "svgAvatars_options", "svgAvatars_validate_options" );
}
add_action( "admin_init", "svgAvatars_admin_init" );

// Display a "Settings" link on the main Plugins page
function svgAvatars_add_plugin_settings_link( $links ) {
	return array_merge(
		array( "settings" => '<a href="' . esc_url( admin_url( "options-general.php?page=svg-avatars-generator/svg-avatars-generator.php" ) ) . '">' . esc_html__( "Settings" ) . "</a>" ),
		$links
	);
}
add_filter( "plugin_action_links_" . SVGAVATARS_PLUGIN_SLUG, "svgAvatars_add_plugin_settings_link" );

// Display a more plugins link in meta
function svgAvatars_more_deethemes_plugins( $links, $file ) {
	if ( $file === SVGAVATARS_PLUGIN_SLUG ) {
		return array_merge(
			$links,
			array( '<a href="https://1.envato.market/c/1301577/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fcollections%2F5913152-wordpress-plugins-by-deethemes" target="_blank">' . esc_html__( "More plugins by DeeThemes", "svg-avatars-generator" ) . "</a>" )
		);
	}
	return $links;
}
add_filter( "plugin_row_meta", "svgAvatars_more_deethemes_plugins", 10, 2 );

// Add plugin menu page
function svgAvatars_add_options_page() {
	$svgAvatars_settings_page = add_options_page(
		esc_html__( "SVG Avatars Generator Plugin Settings", "svg-avatars-generator" ),
		esc_html__( "SVG Avatars", "svg-avatars-generator" ),
		"manage_options",
		SVGAVATARS_FILE,
		"svgAvatars_display_settings"
	);
}
add_action( "admin_menu", "svgAvatars_add_options_page" );

//Visual part of SVG Avatars settings page
function svgAvatars_display_settings() {
	if( ! current_user_can( "manage_options" ) ) {
		wp_die( esc_html__( "You can't manage options.", "svg-avatars-generator" ) );
	}
	$options = get_option( "svgAvatars_options" );
	?>
	<div class="wrap svg-avatars-wrap">
		<h1><?php esc_html_e( "SVG Avatars Generator Settings", "svg-avatars-generator" ) ?></h1>
		<hr>
		<form method="post" action="options.php">
			<?php settings_fields( "svgAvatars_plugin_settings" ); ?>
			<h2><?php esc_html_e( "General options", "svg-avatars-generator" ) ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( "Welcome slogans", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="welcome_slogan_1" class="regular-text" name="svgAvatars_options[welcome_slogan_1]" value="<?php echo esc_attr( $options["welcome_slogan_1"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "The welcome slogan on the starting screen for logged in users, when there is an integration with other plugins.", "svg-avatars-generator" ) ?></p>
							<br>
							<label>
								<input type="text" id="welcome_slogan_2" class="regular-text" name="svgAvatars_options[welcome_slogan_2]" value="<?php echo esc_attr( $options["welcome_slogan_2"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "The welcome slogan for logged out or not registered users, when there is an integration with other plugins.", "svg-avatars-generator" ) ?></p>
							<br>
							<label>
								<input type="text" id="welcome_slogan_3" class="regular-text" name="svgAvatars_options[welcome_slogan_3]" value="<?php echo esc_attr( $options["welcome_slogan_3"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "The welcome slogan, when there is no integration with other plugins, and the \"Save\" button is hidden.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Load on a specific page(s)", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[use_on_spec_page]" type="radio" value="false" <?php checked( "false", $options["use_on_spec_page"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
							<br>
							<label>
								<input name="svgAvatars_options[use_on_spec_page]" type="radio" value="true" <?php checked( "true", $options["use_on_spec_page"] ); ?>> <?php esc_html_e( "Yes, and page slug", "svg-avatars-generator" ) ?>:
								<input type="text" id="page_slug" class="regular-text" name="svgAvatars_options[page_slug]" value="<?php echo esc_attr( $options["page_slug"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "The plugin's styles and scripts can be loaded on a specific page(s) only. This is useful for reducing the load on the site and improving its performance.", "svg-avatars-generator" ) ?></p>
							<p class="description"><?php echo esc_html__( "Enter one or several slugs separated by commas. For example, the avatars generator runs on", "svg-avatars-generator" ) . ' <strong>www.your-site.tld/avatars-generator/</strong> ' . esc_html__( 'page. In that case the slug should be', 'svg-avatars-generator' ) . ' <strong>avatars-generator</strong>'; ?></p>
							<p class="description"><?php echo esc_html__( "If the generator is also used on another page:", "svg-avatars-generator" ) . ' <strong>www.your-site.tld/one-more-page/</strong>, ' . esc_html__( 'then the slug value above should be', 'svg-avatars-generator' ) . ' <strong>avatars-generator, one-more-page</strong>'; ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow genders", "svg-avatars-generator" ) ?></th>
						<td>
							<p>
								<label>
									<input name="svgAvatars_options[show_gender]" type="radio" value="both" <?php checked( "both", $options["show_gender"] ); ?>> <?php esc_html_e( "Both", "svg-avatars-generator" ) ?>
								</label>
							</p>
							<p>
								<label>
									<input name="svgAvatars_options[show_gender]" type="radio" value="boysonly" <?php checked( "boysonly", $options["show_gender"] ); ?>> <?php esc_html_e( "Males only", "svg-avatars-generator" ) ?>
								</label>
							</p>
							<p>
								<label>
									<input name="svgAvatars_options[show_gender]" type="radio" value="girlsonly" <?php checked( "girlsonly", $options["show_gender"] ); ?>> <?php esc_html_e( "Females only", "svg-avatars-generator" ) ?>
								</label>
							</p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Initial avatar zooming", "svg-avatars-generator" ) ?></label>
						</th>
						<td>
							<select name="svgAvatars_options[zooming]">
								<option value="three_steps_upscale" <?php selected( $options["zooming"], "three_steps_upscale" ); ?>><?php esc_html_e( "three steps upscale", "svg-avatars-generator" ) ?></option>
								<option value="two_steps_upscale" <?php selected( $options["zooming"], "two_steps_upscale" ); ?>><?php esc_html_e( "two steps upscale", "svg-avatars-generator" ) ?></option>
								<option value="one_step_upscale" <?php selected( $options["zooming"], "one_step_upscale" ); ?>><?php esc_html_e( "one step upscale", "svg-avatars-generator" ) ?></option>
								<option value="not_changed" <?php selected( $options["zooming"], "not_changed" ); ?>><?php esc_html_e( "not changed", "svg-avatars-generator" ) ?></option>
								<option value="one_step_downscale" <?php selected( $options["zooming"], "one_step_downscale" ); ?>><?php esc_html_e( "one step downscale", "svg-avatars-generator" ) ?></option>
								<option value="two_steps_downscale" <?php selected( $options["zooming"], "two_steps_downscale" ); ?>><?php esc_html_e( "two steps downscale", "svg-avatars-generator" ) ?></option>
								<option value="three_steps_downscale" <?php selected( $options["zooming"], "three_steps_downscale" ); ?>><?php esc_html_e( "three steps downscale", "svg-avatars-generator" ) ?></option>
							</select>
							<p class="description"><?php esc_html_e( "On first init, avatar can be set up to three times bigger or smaller.", "svg-avatars-generator" ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow users to upload created avatars to secure.gravatar.com", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[hide_gravatar]" type="radio" value="false" <?php checked( "false", $options["hide_gravatar"] ); ?>> <?php esc_html_e( "Yes, and size", "svg-avatars-generator" ) ?>:
								<input class="small-text" type="number" step="1" min="100" id="gravatar_size" name="svgAvatars_options[gravatar_size]" value="<?php echo esc_attr( $options["gravatar_size"] ); ?>">
								<span class="description"><?php esc_html_e( "pixels", "svg-avatars-generator" ) ?></span>
							</label><br>
							<label>
								<input name="svgAvatars_options[hide_gravatar]" type="radio" value="true" <?php checked( "true", $options["hide_gravatar"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Remove link to svgAvatars.com", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="radio" name="svgAvatars_options[remove_my_credit]" value="false" <?php checked( "false", $options["remove_my_credit"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>&nbsp;
							<label>
								<input type="radio" name="svgAvatars_options[remove_my_credit]" value="true" <?php checked( "true", $options["remove_my_credit"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?>
							</label>
							<p class="description"><?php esc_html_e( "It's really appreciated if you decide to leave it (rel=\"nofollow\" is already included)", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Your custom CSS file", "svg-avatars-generator" ) ?></th>
						<td>
							<?php esc_html_e( "You can", "svg-avatars-generator" ) ?> <a href="plugin-editor.php?file=svg-avatars-generator/data/css/svgavatars-custom.css&plugin=<?php echo SVGAVATARS_PLUGIN_SLUG;?>" target="_blank"><?php esc_html_e( "add here", "svg-avatars-generator" ) ?></a> <?php esc_html_e( "your custom CSS rules to override existing ones", "svg-avatars-generator" ) ?></span>
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<h2><?php esc_html_e( "Download options", "svg-avatars-generator" ) ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( "Default file name", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="downloading_name" name="svgAvatars_options[downloading_name]" value="<?php echo esc_attr( $options["downloading_name"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "The default file name for downloaded avatars. Should contain the letters and numbers only.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow users to download PNG with small dimensions", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[hide_png_one_download]" type="radio" value="false" <?php checked( "false", $options["hide_png_one_download"] ); ?>> <?php esc_html_e( "Yes, and size", "svg-avatars-generator" ) ?>:
								<input class="small-text" type="number" step="1" min="20" id="png_one_download_size" name="svgAvatars_options[png_one_download_size]" value="<?php echo esc_attr( $options["png_one_download_size"] ); ?>">
								<span class="description"><?php esc_html_e( "pixels", "svg-avatars-generator" ) ?></span>
							</label>
							<br>
							<label>
								<input name="svgAvatars_options[hide_png_one_download]" type="radio" value="true" <?php checked( "true", $options["hide_png_one_download"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow users to download PNG with big dimensions", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[hide_png_two_download]" type="radio" value="false" <?php checked( "false", $options["hide_png_two_download"] ); ?>> <?php esc_html_e( "Yes, and size", "svg-avatars-generator" ) ?>:
								<input class="small-text" type="number" step="1" min="100" id="png_two_download_size" name="svgAvatars_options[png_two_download_size]" value="<?php echo esc_attr( $options["png_two_download_size"] ); ?>">
								<span class="description"><?php esc_html_e( "pixels", "svg-avatars-generator" ) ?></span>
							</label>
							<br>
							<label>
								<input name="svgAvatars_options[hide_png_two_download]" type="radio" value="true" <?php checked( "true", $options["hide_png_two_download"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow users to download SVG file", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[hide_svg_download]" type="radio" value="false" <?php checked( "false", $options["hide_svg_download"] ); ?>> <?php esc_html_e( "Yes, and size", "svg-avatars-generator" ) ?>:
								<input class="small-text" type="number" step="1" min="20" id="svg_download_size" name="svgAvatars_options[svg_download_size]" value="<?php echo esc_attr( $options["svg_download_size"] ); ?>">
								<span class="description"><?php esc_html_e( "conventional value in pixels", "svg-avatars-generator" ) ?></span>
							</label><br>
							<label>
								<input name="svgAvatars_options[hide_svg_download]" type="radio" value="true" <?php checked( "true", $options["hide_svg_download"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "The special PNG file dimensions for iOS devices", "svg-avatars-generator" ) ?></th>
						<td>
							<input class="small-text" type="number" step="1" min="20" id="png_ios_download_size" name="svgAvatars_options[png_ios_download_size]" value="<?php echo esc_attr( $options["png_ios_download_size"] ); ?>">
							<span class="description"><?php esc_html_e( "pixels", "svg-avatars-generator" ) ?></span>
							<p class="description"><?php esc_html_e( "Will take effect if you allow to download avatars in PNG and/or SVG formats.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "The special PNG file dimensions for Windows 8 tablets and phones", "svg-avatars-generator" ) ?></th>
						<td>
							<input class="small-text" type="number" step="1" min="20" id="png_win8tablet_download_size" name="svgAvatars_options[png_win8tablet_download_size]" value="<?php echo esc_attr( $options["png_win8tablet_download_size"] ); ?>">
							<span class="description"><?php esc_html_e( "pixels", "svg-avatars-generator" ) ?></span>
							<p class="description"><?php esc_html_e( "Will take effect if you allow to download avatars in PNG and/or SVG formats.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Disable SVG download option for Android devices", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[hide_svg_download_on_Android]" type="radio" value="true" <?php checked( "true", $options["hide_svg_download_on_Android"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?>
							</label>&nbsp;
							<label>
								<input name="svgAvatars_options[hide_svg_download_on_Android]" type="radio" value="false" <?php checked( "false", $options["hide_svg_download_on_Android"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
							<p class="description"><?php esc_html_e( "Usually SVG format is not supported by Android Apps.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<h2><?php esc_html_e( "Save options", "svg-avatars-generator" ) ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( "Show SVG Avatars Generator on user profile page in WP Dashboard", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[show_in_backend]" type="radio" value="true" <?php checked( "true", $options["show_in_backend"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?>
							</label>&nbsp;
							<label>
								<input name="svgAvatars_options[show_in_backend]" type="radio" value="false" <?php checked( "false", $options["show_in_backend"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Size of saved avatars", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input class="small-text" type="number" step="1" min="20" id="save_size" name="svgAvatars_options[save_size]" value="<?php echo esc_attr( $options["save_size"] ); ?>"> <?php esc_html_e( "pixels", "svg-avatars-generator" ) ?>

							</label>
							<p class="description"><?php esc_html_e( "Note: when integrated, some third party plugins have own settings of avatar sizes.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Saved file format", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[save_format]" type="radio" value="png" <?php checked( "png", $options["save_format"] ); ?>> PNG
							</label>
							&nbsp;
							<label>
								<input name="svgAvatars_options[save_format]" type="radio" value="svg" <?php checked( "svg", $options["save_format"] ); ?>> SVG
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Integration (with)", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="none" <?php checked( "none", $options["integration"] ); ?>> <?php esc_html_e( "None", "svg-avatars-generator" ) ?>
							</label>
							<p class="description"><?php esc_html_e( "The \"Save\" button of SVG avatars generator will be hidden.", "svg-avatars-generator" ) ?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="custom" <?php checked( "custom", $options["integration"] ); ?>> <?php esc_html_e( "Custom", "svg-avatars-generator" ) ?>
							</label>
							<p class="description"><?php esc_html_e( "Your custom function controls avatars saving. Please add your code into the appropriate section of the \"svg-avatars-save-avatar.php\" file.", "svg-avatars-generator" ) ?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="local" <?php checked( "local", $options["integration"] ); ?>> <?php esc_html_e( "Local", "svg-avatars-generator" ) ?>
							</label>
							<p class="description"><?php esc_html_e( "The gravatars on this site will be replaced with local avatars, which are saved in the 'path_to/wp-content/uploads/svg-avatars/X/' directory, where 'X' is an integer equal to the user ID.", "svg-avatars-generator" ) ?>
								<br>
								<?php $url = esc_url( "https://bbpress.org/" );
								$desc = sprintf( esc_html__( "bbPress forum", "svg-avatars-generator" ) );
								$link = sprintf( '<a target="_blank" href="%1$s">%2$s</a><span class="dashicons dashicons-external"></span>', $url, $desc );
								printf( esc_html__("This option also takes effect on integration with %splugin", "svg-avatars-generator" ), $link );
								?>
							</p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="BuddyBoss" <?php checked( "BuddyBoss", $options["integration"] ); ?>> BuddyBoss
							</label>
							<p class="description">
								<?php $url = esc_url( "https://www.buddyboss.com/platform/" );
								$link = sprintf( '<a target="_blank" href="%1$s">BuddyBoss Platform</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?>
								<br>
								<?php
								// BuddyBoss is installed and active
								if ( function_exists( "bp_core_set_avatar_constants" ) && function_exists( "bp_core_xprofile_update_profile_completion_user_progress" ) ) {
									printf( esc_html__('The plugin has its own settings of avatar sizes. By now they are %1$sx%2$s px for "full" avatars and %3$sx%4$s px for "thumb" ones.', "svg-avatars-generator" ), BP_AVATAR_FULL_WIDTH, BP_AVATAR_FULL_HEIGHT, BP_AVATAR_THUMB_WIDTH, BP_AVATAR_THUMB_HEIGHT );
								} else {
									esc_html_e('The plugin has its own settings of avatar sizes. By default they are 150x150 px for "full" avatars and 50x50 px for "thumb" ones.', "svg-avatars-generator");
								}
								?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="BuddyPress" <?php checked( "BuddyPress", $options["integration"] ); ?>> BuddyPress
							</label>
							<p class="description">
								<?php $url = esc_url( "https://buddypress.org/" );
								$link = sprintf( '<a target="_blank" href="%1$s">BuddyPress</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?>
								<br>
								<?php
								// ByddyPress is installed and active
								if ( function_exists( "bp_core_set_avatar_constants" ) && ! function_exists( "bp_core_xprofile_update_profile_completion_user_progress" ) ) {
									printf( esc_html__('The plugin has its own settings of avatar sizes. By now they are %1$sx%2$s px for "full" avatars and %3$sx%4$s px for "thumb" ones.', "svg-avatars-generator" ), BP_AVATAR_FULL_WIDTH, BP_AVATAR_FULL_HEIGHT, BP_AVATAR_THUMB_WIDTH, BP_AVATAR_THUMB_HEIGHT );
								} else {
									esc_html_e('The plugin has its own settings of avatar sizes. By default they are 150x150 px for "full" avatars and 50x50 px for "thumb" ones.', "svg-avatars-generator");
								}
								?>
								<br>
								<?php $url = esc_url( "https://codecanyon.net/item/youzer-new-wordpress-user-profiles-era/19716647" );
								$desc = sprintf( "Youzer - Buddypress Community & Wordpress User Profile" );
								$link = sprintf( '<a target="_blank" href="%1$s">%2$s</a><span class="dashicons dashicons-external"></span>', $url, $desc );
								printf( esc_html__("This option also takes effect on integration with %splugin", "svg-avatars-generator" ), $link );
								?>
							</p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="LearnPress" <?php checked( "LearnPress", $options["integration"] ); ?>> LearnPress
							</label>
							<p class="description">
								<?php $url = esc_url( "https://wordpress.org/plugins/learnpress/" );
								$link = sprintf( '<a target="_blank" href="%1$s">LearnPress – WordPress LMS</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?>
								<br>
								<?php
								// LearnPress is installed and active
								if ( function_exists( "learn_press_get_avatar_thumb_size" ) ) {
									$learn_press_avatar_sizes = learn_press_get_avatar_thumb_size();
									printf( esc_html__('The plugin has its own settings of avatar sizes. By now they are %1$sx%2$s px.', "svg-avatars-generator" ), $learn_press_avatar_sizes["width"], $learn_press_avatar_sizes["height"] );
								} else {
									esc_html_e('The plugin has its own settings of avatar sizes. By default they are 200x200 px.', "svg-avatars-generator");
								}
								?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="UltimateMember" <?php checked( "UltimateMember", $options["integration"] ); ?>> Ultimate Member
							</label>
							<p class="description">
								<?php $url = esc_url( "https://wordpress.org/plugins/ultimate-member/" );
								$link = sprintf( '<a target="_blank" href="%1$s">Ultimate Member – User Profile, User Registration, Login & Membership</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?>
								<br>
								<?php
								// Ultimate Member is installed and active
								if ( class_exists( 'UM' ) ) {
									$UM_sizes = UM()->options()->get( "photo_thumb_sizes" );
									if ( is_array( $UM_sizes ) ) {
										esc_html_e("The plugin has its own settings of avatar sizes. By now they are ", "svg-avatars-generator" );
										for ( $i = 0; $i < count( $UM_sizes ) - 1; $i++ ) {
											printf( esc_html__('%1$sx%1$s px, ', "svg-avatars-generator" ), $UM_sizes[$i] );
										}
										printf( esc_html__('and %1$sx%1$s px.', "svg-avatars-generator" ), end( $UM_sizes ) );
									}
									unset( $UM_sizes );
								} else {
									esc_html_e('The plugin has its own settings of avatar sizes. By default they are 40x40 px, 80x80 px, and 190x190 px.', "svg-avatars-generator");
								}
								?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="UltimateMembershipPro" <?php checked( "UltimateMembershipPro", $options["integration"] ); ?>> Ultimate Membership Pro
							</label>
							<p class="description">
								<?php $url = esc_url( "https://codecanyon.net/item/ultimate-membership-pro-wordpress-plugin/12159253" );
								$link = sprintf( '<a target="_blank" href="%1$s">Ultimate Membership Pro - WordPress Membership</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="UserPro" <?php checked( "UserPro", $options["integration"] ); ?>> UserPro
							</label>
							<p class="description">
								<?php $url = esc_url( "https://codecanyon.net/item/userpro-user-profiles-with-social-login/5958681" );
								$link = sprintf( '<a target="_blank" href="%1$s">UserPro - Community and User Profile WordPress</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?></p>
							<br>
							<label>
								<input name="svgAvatars_options[integration]" type="radio" value="UPME" <?php checked( "UPME", $options["integration"] ); ?>> User Profiles Made Easy
							</label>
							<p class="description">
								<?php $url = esc_url( "https://codecanyon.net/item/user-profiles-made-easy-wordpress-plugin/4109874" );
								$link = sprintf( '<a target="_blank" href="%1$s">User Profiles Made Easy - WordPress</a><span class="dashicons dashicons-external"></span>', $url );
								printf( esc_html__("%splugin must be installed and activated!", "svg-avatars-generator" ), $link );
								?>
								<br>
								<?php
								// User Profiles Made Easy is installed and active
								if ( class_exists( "UPME" ) ) {
									global $upme_options;
									if ( $upme_options->upme_settings["profile_image_resize_width"] && $upme_options->upme_settings["profile_image_resize_height"] ) {
										printf( esc_html__('The plugin has its own settings of avatar thumbnail sizes. By now they are %1$sx%2$s px.', "svg-avatars-generator" ), $upme_options->upme_settings["profile_image_resize_width"], $upme_options->upme_settings["profile_image_resize_height"] );

									} else {
										esc_html_e('The plugin has its own settings of avatar thumbnail sizes. By default they are 100x100 px.', "svg-avatars-generator");
									}
								} else {
									esc_html_e('The plugin has its own settings of avatar thumbnail sizes. By default they are 100x100 px.', "svg-avatars-generator");
								}
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Add \"Create Avatar\" tab", "svg-avatars-generator" ) ?></th>
						<td>
							<p>
								<label>
									<input type="checkbox" id="add_buddypress_profile_subnav" name="svgAvatars_options[add_buddypress_profile_subnav]" value="true" <?php checked( "true", $options["add_buddypress_profile_subnav"] ); ?>> <?php  esc_html_e( "Yes", "svg-avatars-generator" ) ?>
								</label>
							</p>
							<p class="description">
								<?php esc_html_e( "If checked, the new \"Create Avatar\" tab with SVG avatars generator will be added into the \"Profile\" component.", "svg-avatars-generator" ); ?>
							</p>
							<p class="description">
								<?php echo esc_html__( "Takes effect for", "svg-avatars-generator" ) . ' <strong>BuddyPress</strong> ' . esc_html__( 'plugin and its extensions like', 'svg-avatars-generator' ) . ' <strong>BuddyBoss, Youzer, etc.</strong>'; ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Custom message", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="custom_heading" class="regular-text" name="svgAvatars_options[custom_heading]" value="<?php echo esc_attr( $options["custom_heading"] ); ?>">
								<span class="description"><?php esc_html_e( "(heading)", "svg-avatars-generator" ) ?></span>
							</label>
							<p></p>
							<label>
								<input type="text" id="custom_text" class="regular-text" name="svgAvatars_options[custom_text]" value="<?php echo esc_attr( $options["custom_text"] ); ?>">
								<span class="description"><?php esc_html_e( "(text)", "svg-avatars-generator" ) ?></span>
							</label>
							<p class="description">
								<?php echo esc_html__( "The message of successful avatar saving when", "svg-avatars-generator" ) . ' <strong>' . esc_html__( "Integration", "svg-avatars-generator" ) .'</strong> ' . esc_html__( 'option is set to', 'svg-avatars-generator' ) . ' <strong>' . esc_html__( "Custom", "svg-avatars-generator" ) .'</strong>'; ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<h2><?php esc_html_e( "Share options", "svg-avatars-generator" ) ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( "Allow users to share avatars", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[hide_share]" type="radio" value="false" <?php checked( "false", $options["hide_share"] ); ?>> <?php esc_html_e( "Yes, and size", "svg-avatars-generator" ) ?>:
								<input class="small-text" type="number" step="1" min="200" id="share_image_size" name="svgAvatars_options[share_image_size]" value="<?php echo esc_attr( $options["share_image_size"] ); ?>">
								<span class="description"><?php esc_html_e( "pixels", "svg-avatars-generator" ) ?></span></label><br>
							<label>
								<input name="svgAvatars_options[hide_share]" type="radio" value="true" <?php checked( "true", $options["hide_share"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow share on Pinterest", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[pinterest]" type="radio" value="true" <?php checked( "true", $options["pinterest"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?>
							</label>&nbsp;
							<label>
								<input name="svgAvatars_options[pinterest]" type="radio" value="false" <?php checked( "false", $options["pinterest"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Allow share on Twitter", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input name="svgAvatars_options[twitter]" type="radio" value="true" <?php checked( "true", $options["twitter"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?>
							</label>&nbsp;
							<label>
								<input name="svgAvatars_options[twitter]" type="radio" value="false" <?php checked( "false", $options["twitter"] ); ?>> <?php esc_html_e( "No", "svg-avatars-generator" ) ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "URL for share", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="share_link" class="regular-text" name="svgAvatars_options[share_link]" value="<?php echo esc_url( $options["share_link"] ); ?>"><span class="description"> <?php esc_html_e( "start your custom URL with http:// or https://", "svg-avatars-generator" ) ?></span>
							</label>
							<p class="description"><?php esc_html_e( "If leave it blank, the value will be the document URL of a web page where the generator is placed.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Title for share", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="share_title" class="regular-text" name="svgAvatars_options[share_title]" value="<?php echo esc_html( $options["share_title"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "If leave it blank, the title will be taken from &lt;title&gt; HTML tag of a web page where the generator is placed.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Description for share", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="share_description" class="regular-text" name="svgAvatars_options[share_description]" value="<?php echo esc_html( $options["share_description"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "If leave it blank, it might be taken from your &lt;meta&gt; description tag of a web page where the generator is placed.", "svg-avatars-generator") ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Watermark on avatars for share", "svg-avatars-generator" ) ?></th>
						<td>
							<label>
								<input type="text" id="share_credit" name="svgAvatars_options[share_credit]" value="<?php echo esc_html( $options["share_credit"] ); ?>">
							</label>
							<p class="description"><?php esc_html_e( "If leave it blank, no watermark will be added", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<h2><?php esc_html_e( "Color calculation and color scheme", "svg-avatars-generator" ) ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( "Delta (difference) of saturation:", "svg-avatars-generator" ) ?></th>
						<td>
							<input class="small-text" type="number" step="1" min="0" max="100" id="delta_sat" name="svgAvatars_options[delta_sat]" value="<?php echo esc_attr( $options["delta_sat"] );?>">
							<span class="description"><?php esc_html_e( " %, range from 0 to 100", "svg-avatars-generator" ) ?></span>
							<p class="description"><?php esc_html_e( "It's used for automatic calculating shadows and highlights", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Delta (difference) of brightness", "svg-avatars-generator" ) ?></th>
						<td>
							<input class="small-text" type="number" step="1" min="0" max="100" id="delta_val" name="svgAvatars_options[delta_val]" value="<?php echo esc_attr( $options["delta_val"] );?>">
							<span class="description"><?php esc_html_e( " %, range from 0 to 100", "svg-avatars-generator" ) ?></span>
							<p class="description"><?php esc_html_e( "It's used for automatic calculating shadows and highlights", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Color scheme (skin)", "svg-avatars-generator" ) ?></th>
						<td>
							<p>
								<label>
									<input name="svgAvatars_options[color_theme]" type="radio" value="dark" <?php checked( "dark", $options["color_theme"] ); ?>> <?php esc_html_e( "Dark", "svg-avatars-generator" ) ?>
								</label>
							</p>
							<p>
								<label>
									<input name="svgAvatars_options[color_theme]" type="radio" value="light" <?php checked( "light", $options["color_theme"] ); ?>> <?php esc_html_e( "Light", "svg-avatars-generator" ) ?>
								</label>
							</p>
							<p>
								<label>
									<input name="svgAvatars_options[color_theme]" type="radio" value="custom" <?php checked( "custom", $options["color_theme"] ); ?>> <?php esc_html_e( "Custom", "svg-avatars-generator" ) ?>
									&nbsp;&nbsp;<span class="description"> <a href="plugin-editor.php?file=svg-avatars-generator/data/css/svgavatars-custom-colors.css&plugin=<?php echo SVGAVATARS_PLUGIN_SLUG;?>" target="_blank"><?php esc_html_e( "Click here", "svg-avatars-generator") ?></a> <?php esc_html_e( "to edit CSS file with custom colors (repeats Light scheme for reference by default).", "svg-avatars-generator" ) ?></span>
								</label>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<h2><?php esc_html_e( "Database and service options", "svg-avatars-generator" ) ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( "Restore Factory Defaults", "svg-avatars-generator" ) ?></th>
						<td>
							<p>
								<label><input type="checkbox" id="reset_to_default_options" name="svgAvatars_options[reset_to_default_options]" value="true" <?php checked( "true", $options["reset_to_default_options"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?></label>
							</p>
							<p class="description"><?php esc_html_e( "If checked, reset all settings to the factory default values.", "svg-avatars-generator" ) ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Delete all options on Uninstall", "svg-avatars-generator" ) ?></th>
						<td>
							<p>
								<label><input type="checkbox" id="delete_settings_on_uninstall" name="svgAvatars_options[delete_settings_on_uninstall]" value="true" <?php checked( "true", $options["delete_settings_on_uninstall"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?></label>
							</p>
							<p class="description">
								<?php echo esc_html__( "If checked, remove all the SVG Avatars data from the database when using the", "svg-avatars-generator" ) . ' <strong>' . esc_html__( "Delete", "svg-avatars-generator" ) .'</strong> ' . esc_html__( 'link on the main Plugins page.', 'svg-avatars-generator' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( "Debug mode", "svg-avatars-generator" ) ?></th>
						<td>
							<p>
								<label><input type="checkbox" id="debug" name="svgAvatars_options[debug]" value="true" <?php checked( "true", $options["debug"] ); ?>> <?php esc_html_e( "Yes", "svg-avatars-generator" ) ?></label>
							</p>
							<p class="description">
								<?php esc_html_e( "If checked, show service and error messages in JavaScript Console (F12 key in Google Chrome, Firefox, and MS Edge) on client side.", "svg-avatars-generator" ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<?php submit_button( esc_html__( "Save Changes", "svg-avatars-generator" ), "primary", "submit", false, array( "id" => "submit_1" ) ); ?>
			</p>
		</form>
		<hr>
		<h2><?php esc_html_e( "Settings Import and Export", "svg-avatars-generator" ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php esc_html_e( "Export Settings", "svg-avatars-generator" ); ?></th>
					<td>
						<p>
							<?php esc_html_e( "Export the plugin settings of this site as a JSON file. This allows you to easily restore the settings and/or import them into another site.", "svg-avatars-generator" ); ?>
						</p>
						<form method="post">
							<p>
								<input type="hidden" name="svgAvatars_im_ex_action" value="export_settings" />
							</p>
							<p class="submit">
								<?php wp_nonce_field( "svgAvatars_export_nonce", "svgAvatars_export_nonce" ); ?>
								<?php submit_button( esc_html__( "Export", "svg-avatars-generator" ), "secondary", "submit", false, array( "id" => "submit_2" ) ); ?>
							</p>
						</form>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( "Import Settings", "svg-avatars-generator" ); ?></th>
					<td>
						<p>
							<?php echo esc_html__( "Import the plugin settings from a", "svg-avatars-generator" ) . ' <strong>*.json</strong> ' . esc_html__( 'file. This file can be obtained by exporting the settings of this site or another one using the form above.', 'svg-avatars-generator' ); ?>

						</p>
						<form method="post" enctype="multipart/form-data">
							<p>
								<input type="file" name="import_file"/>
								<input type="hidden" name="svgAvatars_im_ex_action" value="import_settings" />
							</p>
							<p class="submit">
								<?php wp_nonce_field( "svgAvatars_import_nonce", "svgAvatars_import_nonce" ); ?>
								<?php submit_button( esc_html__( "Import", "svg-avatars-generator" ), "secondary", "submit", false, array( "id" => "submit_3" ) ); ?>
							</p>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
		<hr>
		<h2><?php esc_html_e( "Manage temporary avatars", "svg-avatars-generator" ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php esc_html_e( "Clear avatars", "svg-avatars-generator" ); ?></th>
					<td>
						<p>
							<?php esc_html_e( "Delete all temporary avatars files in the folder:", "svg-avatars-generator" ); ?> <strong><?php echo SVGAVATARS_PATH . "data/temp-avatars/"?></strong>
						</p>
						<p>
							<?php $count_result = svgAvatars_count_temporary_files();?>
							<?php esc_html_e( "Total files:", "svg-avatars-generator" ); ?> <strong><?php echo $count_result["count"];?></strong>
						</p>
						<p>
							<?php esc_html_e( "Total size:", "svg-avatars-generator" ); ?> <strong><?php echo $count_result["size"];?></strong>
						</p>
						<form method="post">
							<p>
								<input type="hidden" name="svgAvatars_clear_action" value="clear_avatars" />
							</p>
							<p class="submit">
								<?php wp_nonce_field( "svgAvatars_clear_nonce", "svgAvatars_clear_nonce" ); ?>
								<?php if ( $count_result["count"] > 0 ) {
										submit_button( esc_html__( "Clear", "svg-avatars-generator" ), "secondary", "submit", false, array( "id" => "submit_4" ) );
									} else {
										submit_button( esc_html__( "Clear", "svg-avatars-generator" ), "secondary", "submit", false, array( "id" => "submit_4", "disabled" => "disabled" ) );
									}
								?>
							</p>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

<?php
}

// Clear temporary avatars
// count number of files and their size in the temporary folder
function svgAvatars_count_temporary_files() {
	//function works with the "path_to/wp-content/plugins/svg-avatars-generator/data/temp-avatars" only
	$dir = SVGAVATARS_PATH . "data/temp-avatars/";
	$result = array(
		"count" => 0,
		"size" => 0
	);
	global $wp_filesystem;
	if ( $wp_filesystem->is_dir( $dir ) ) {
		$arr = $wp_filesystem->dirlist( $dir );
		foreach ( $arr as $file ) {
			$file['name'] = str_replace( "\\", "/", $file['name'] ); // for win32, occasional problems deleting files otherwise
			if ( $file['name'] !== "." && $file['name'] !== ".." && $file['name'] !== "index.php" && $file['type'] !== 'd' ) {
				$result["count"]++;
				$result["size"] += $file['size'];
			}
		}
		unset( $file );
	}
	if ( $result["size"] <= 1024 ) {
		$result["size"] = $result["size"] . " bytes";
	} elseif ( $result["size"] <= 1024 * 1024 ) {
		$result["size"] = round( $result["size"] / 1024, 2 ) . " Kb";
	} elseif ( $result["size"] <= 1024 * 1024 * 1024 ) {
		$result["size"] = round( $result["size"] / ( 1024 * 1024 ), 2 ) . " Mb";
	} elseif ( $result["size"] <= 1024 * 1024 * 1024 * 1024 ) {
		$result["size"] = round( $result["size"] / ( 1024 * 1024 * 1024 ), 2 ) . " Gb";
	} else {
		$result["size"] = $result["size"] . " bytes";
	}
	return $result;
}
function svgAvatars_delete_files() {
	//function works with the "path_to/wp-content/plugins/svg-avatars-generator/data/temp-avatars" only
	$dir = SVGAVATARS_PATH . "data/temp-avatars/";
	global $wp_filesystem;
	if ( $wp_filesystem->is_dir( $dir ) ) {
		$arr = $wp_filesystem->dirlist( $dir );
		foreach ( $arr as $file ) {
			$file['name'] = str_replace( "\\", "/", $file['name'] ); // for win32, occasional problems deleting files otherwise
			if ( $file['name'] !== "." && $file['name'] !== ".." && $file['name'] !== "index.php" && $file['type'] !== 'd' ) {
				$wp_filesystem->delete( $dir . $file['name'] );
			}
		}
		unset( $file );
		return true;
	} else {
		return false;
	}
}
function svgAvatars_clear_temporary_avatars() {
	if( empty( $_POST["svgAvatars_clear_action"] ) || "clear_avatars" !== $_POST["svgAvatars_clear_action"] ) {
		return;
	}
	if( ! wp_verify_nonce( $_POST["svgAvatars_clear_nonce"], "svgAvatars_clear_nonce" ) ) {
		return;
	}
	if( ! current_user_can( "manage_options" ) ) {
		return;
	}
	$result = svgAvatars_delete_files();
	//save a temporary message
	if( $result === true ) {
		set_transient(
			"svgAvatars_clear_avatars_result",
			esc_html__( "The temporary avatars have been deleted successfully.", "svg-avatars-generator" ),
			MINUTE_IN_SECONDS
		);
		set_transient(
			"svgAvatars_clear_avatars_result_notice_flag",
			"notice-success",
			MINUTE_IN_SECONDS
		);
	} else {
		set_transient(
			"svgAvatars_clear_avatars_result",
			esc_html__( "An error occurred. The temporary avatars have not been deleted. Please check the folder exists and its permissions.", "svg-avatars-generator" ),
			MINUTE_IN_SECONDS
		);
		set_transient(
			"svgAvatars_clear_avatars_result_notice_flag",
			"notice-error",
			MINUTE_IN_SECONDS
		);
	}
	wp_safe_redirect( admin_url( "options-general.php?page=svg-avatars-generator/svg-avatars-generator.php" ) );
	exit;
}
add_action( "admin_init", "svgAvatars_clear_temporary_avatars" );
// display a message of a result of deleting avatars
function svgAvatars_admin_notices_clear_temporary_avatars() {
	$message = get_transient( "svgAvatars_clear_avatars_result" );
	$flag = get_transient( "svgAvatars_clear_avatars_result_notice_flag" );
	if ( $message ) {
		delete_transient( "svgAvatars_clear_avatars_result" );
		if ( $flag ) {
			delete_transient( "svgAvatars_clear_avatars_result_notice_flag" );
		}
		printf( '<div class="%1$s %2$s"><p>%3$s</p></div>',
			"notice is-dismissible",
			$flag,
			$message
		);
	}
}
add_action( "admin_notices", "svgAvatars_admin_notices_clear_temporary_avatars" );

// Settings export generates a .json file of all the settings
function svgAvatars_settings_export() {
	if( empty( $_POST["svgAvatars_im_ex_action"] ) || "export_settings" !== $_POST["svgAvatars_im_ex_action"] ) {
		return;
	}
	if( ! wp_verify_nonce( $_POST["svgAvatars_export_nonce"], "svgAvatars_export_nonce" ) ) {
		return;
	}
	if( ! current_user_can( "manage_options" ) ) {
		return;
	}

	$settings = get_option( "svgAvatars_options" );

	ignore_user_abort( true );
	nocache_headers();
	header( "Content-Type: application/json; charset=utf-8" );
	header( "Content-Disposition: attachment; filename=svgavatars-settings-export-" . date( "F-d-Y" ) . ".json" );
	header( "Expires: 0" );
	echo json_encode( $settings, JSON_PRETTY_PRINT );
	exit;
}
add_action( "admin_init", "svgAvatars_settings_export" );

// Settings import from a .json file
function svgAvatars_settings_import() {
	if( empty( $_POST["svgAvatars_im_ex_action"] ) || "import_settings" !== $_POST["svgAvatars_im_ex_action"] ) {
		return;
	}
	if( ! wp_verify_nonce( $_POST["svgAvatars_import_nonce"], "svgAvatars_import_nonce" ) ) {
		return;
	}
	if( ! current_user_can( "manage_options" ) ) {
		return;
	}
	$import_file = $_FILES["import_file"]["tmp_name"];
	if( empty( $import_file ) ) {
		wp_die( esc_html__( "Please choose a file to import", "svg-avatars-generator" ) );
	}
	$temp = explode( ".", $_FILES["import_file"]["name"] );
	$extension = end( $temp );
	if( $extension !== "json" ) {
		wp_die( esc_html__( "Please upload a valid .json file", "svg-avatars-generator" ) );
	}

	// retrieve the settings from the file
	global $wp_filesystem;
	$settings = json_decode( $wp_filesystem->get_contents( $import_file ), true );
	update_option( "svgAvatars_options", $settings );

	// save a temporary message
	set_transient(
		"svgAvatars_import_result",
		esc_html__( "The plugin settings were successfully imported.", "svg-avatars-generator" ),
		MINUTE_IN_SECONDS
	);

	wp_safe_redirect( admin_url( "options-general.php?page=svg-avatars-generator/svg-avatars-generator.php" ) );
	exit;
}
add_action( "admin_init", "svgAvatars_settings_import" );

// Display a message of import.
function svgAvatars_admin_notices_import_result() {
	$message = get_transient( "svgAvatars_import_result" );
	if ( $message ) {
		delete_transient( "svgAvatars_import_result" );
		printf( '<div class="%1$s"><p>%2$s</p></div>',
			"notice notice-success is-dismissible",
			$message
		);
	}
}
add_action( "admin_notices", "svgAvatars_admin_notices_import_result" );
