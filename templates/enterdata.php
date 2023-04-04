<div class="wpopenai-enter-api-key-box centered">
	<h2><?php esc_html_e( 'Enter your OpenAI API key', 'wpopenai' ); ?></h2>
	<div class="enter-api-key">
		<form id="gpt_option_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
			<div class="apikey-contianer">
				<input type="hidden" name="action" value="save_my_plugin_settings">
				<p style="width: 100%; display: flex; flex-wrap: nowrap; box-sizing: border-box;">
					<input id="openkey" name="openkey" type="text" size="15" value="<?php echo esc_attr(get_option('gcgoai_wpopenai_key')); ?>" placeholder="<?php esc_attr_e( 'Enter your API key' , 'wpopenai' ); ?>" class="regular-text code" style="flex-grow: 1; margin-right: 1rem;">
				</p>
			</div>
			<!-- Post Type based condition. ( HTML ) -->
			<div class="post_type_container">
				<?php 
					$post_types = get_post_types( array( 'public' => true ), 'objects' );
					unset( $post_types['attachment'] );
					$visibilityPostType = get_option('gcgoai_generate_visibility');
					foreach ($post_types as $post_type) {
						$select_id = 'post-type-' . $post_type->name . '-select';
						$label = $post_type->labels->name;
						$value = isset($visibilityPostType[$post_type->name]) ? $visibilityPostType[$post_type->name] : 'hide';
						?>
						<div class="selectbox_row">
							<label class="posttype_label" for="<?php echo $select_id; ?>"><?php echo $label; ?></label>
							<select class="visi-selectbox" id="<?php echo $select_id; ?>" name="<?php echo $post_type->name; ?>">
								<option <?php selected($value, 'hide'); ?> value="hide">Hide</option>
								<option <?php selected($value, 'show'); ?> value="show">Show</option>
							</select>
						</div>
						<?php 
					}
				?>
			</div>			
			<div class="save_button_row">
				<input type="submit" name="submitkey" id="submitkey" class="wpopenai-button" value="<?php esc_attr_e( 'Save Changes', 'wpopenai' );?>">
			</div>
		</form>
	</div>
</div>