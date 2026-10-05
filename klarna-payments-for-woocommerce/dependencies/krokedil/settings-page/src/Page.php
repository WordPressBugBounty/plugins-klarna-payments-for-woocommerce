<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

use KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits\Layout;
\defined('ABSPATH') || exit;
/**
 * Main class for the settings page package.
 */
class Page
{
    use Layout;
    /**
     * The content for the page.
     *
     * @var string $content
     */
    protected $content = '';
    /**
     * Class constructor.
     *
     * @param string               $content The content for the page.
     * @param array<string, mixed> $sidebar Sidebar content.
     *
     * @return void
     */
    public function __construct(string $content, array $sidebar)
    {
        $this->content = $content;
        $this->sidebar = $sidebar;
    }
    /**
     * Output the layout.
     *
     * @return void
     */
    public function output() : void
    {
        wp_enqueue_style('krokedil-settings-page');
        ?>
		<div class="krokedil_settings__custom_page">
			<div class="krokedil_settings__wrapper">
				<?php 
        $this->output_subsection();
        ?>
				<?php 
        $this->output_sidebar();
        ?>
			</div>
		</div>
		<?php 
    }
    /**
     * Output the page HTML.
     *
     * @return void
     */
    public function output_page_content() : void
    {
        echo $this->content;
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
