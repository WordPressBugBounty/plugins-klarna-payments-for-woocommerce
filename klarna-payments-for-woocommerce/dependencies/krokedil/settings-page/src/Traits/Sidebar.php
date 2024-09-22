<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits;

trait Sidebar
{
    /**
     * The sidebar content.
     *
     * @var array $sidebar
     */
    protected $sidebar = array();
    /**
     * Output the Sidebar.
     *
     * @return void
     */
    public function output_sidebar()
    {
        $plugin_resources = $this->sidebar['plugin_resources']['links'];
        $additional_resources = $this->sidebar['additional_resources']['links'];
        $krokedil_url = get_locale() === 'sv_SE' ? 'https://krokedil.se/' : 'https://krokedil.com/';
        // Get the locale of the site but convert it to lowercase 2 letter language code.
        ?>
			<div class="krokedil_settings__sidebar">
				<div class="krokedil_settings__sidebar_section">
					<div class="krokedil_settings__sidebar_content">
						<h1 class="krokedil_settings__sidebar_title"><?php 
        echo esc_html(__('Plugin resources', 'krokedil-settings'));
        ?></h1>
						<p class="krokedil_settings__sidebar_main_text">
							<?php 
        foreach ($plugin_resources as $link) {
            ?>
								<span>
									&raquo;
									<?php 
            echo wp_kses_post(self::get_link($link));
            ?>
								</span>
							<?php 
        }
        ?>
						</p>
						<h1 class="krokedil_settings__sidebar_title"><?php 
        echo esc_html(__('Additional resources', 'krokedil-settings'));
        ?></h1>
						<p class="krokedil_settings__sidebar_main_text">
							<?php 
        foreach ($additional_resources as $link) {
            ?>
								<span>
									&raquo;
									<?php 
            echo wp_kses_post(self::get_link($link));
            ?>
								</span>
							<?php 
        }
        ?>
						</p>
					</div>
					<div class="krokedil_settings__sidebar_footer">
						<p class="krokedil_settings__sidebar_subtext">
							Developed by:
						</p>
						<a class="no-external-icon" href="<?php 
        echo esc_url($krokedil_url);
        ?>" target="_blank">
							<img class="krokedil_settings__sidebar_logo"
								src="https://krokedil.se/wp-content/uploads/2020/05/webb_logo_400px.png">
						</a>
					</div>
			</div>
		<?php 
    }
}
