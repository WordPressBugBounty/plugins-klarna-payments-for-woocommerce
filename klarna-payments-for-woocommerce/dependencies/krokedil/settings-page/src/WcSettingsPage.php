<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

\defined('ABSPATH') || exit;
use KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits\Layout;
/**
 * Class for adding the settings page styling and functionality to a WooCommerce settings page.
 * Can be extended to create custom settings pages for specific things, like gateways or shipping.
 */
class WcSettingsPage
{
    use Layout;
    /**
     * The ID for the page.
     *
     * @var string $id
     */
    protected $id;
    /**
     * Arguments for the page.
     *
     * @var array<string, mixed> $args
     */
    protected $args;
    /**
     * Icon for the gateway.
     *
     * @var string $icon
     */
    protected $icon;
    /**
     * Whether to display page navigation as a sidebar for the settings sections of the current page.
     *
     * @var bool $settings_navigation
     */
    protected $settings_navigation;
    /**
     * Whether to style the output for the settings page.
     *
     * @var bool $styled_output
     */
    protected $styled_output;
    /**
     * Class constructor.
     *
     * @param string               $id The ID for the page.
     * @param array<string, mixed> $args Arguments for the page.
     * @param array<string, mixed> $form_fields The form fields for the settings page.
     *
     * @return void
     */
    public function __construct(string $id, array $args = array(), array $form_fields = array())
    {
        $this->id = $id;
        $this->args = $args;
        $this->icon = $args['icon'] ?? 'img.png';
        $this->sidebar = $args['sidebar'] ?? array();
        $this->settings_navigation = $args['settings_navigation'] ?? \false;
        $this->styled_output = $args['styled_output'] ?? \false;
        $this->page_title = $args['page_title'] ?? 'Settings';
        $this->page_description = $args['page_description'] ?? '';
        $this->back_link_label = $args['back_link_label'] ?? '';
        $this->back_link_url = $args['back_link_url'] ?? '';
        $this->form_fields = $form_fields;
        if ($this->styled_output) {
            add_filter('woocommerce_generate_krokedil_section_start_html', array(__CLASS__, 'krokedil_section_start_html'), 10, 3);
            add_filter('woocommerce_generate_krokedil_section_end_html', array(__CLASS__, 'krokedil_section_end_html'), 10, 3);
            add_filter('woocommerce_generate_krokedil_button_html', array(__CLASS__, 'krokedil_button_html'), 10, 3);
            add_filter('woocommerce_generate_krokedil_divider_html', array(__CLASS__, 'krokedil_divider_html'), 10, 3);
            add_filter('woocommerce_generate_krokedil_radio_html', array(__CLASS__, 'krokedil_radio_html'), 10, 3);
            add_filter('woocommerce_admin_field_krokedil_section_start', array(__CLASS__, 'krokedil_section_start'), 10, 1);
            add_filter('woocommerce_admin_field_krokedil_section_end', array(__CLASS__, 'krokedil_section_end'), 10, 1);
            add_filter('woocommerce_admin_field_krokedil_button', array(__CLASS__, 'krokedil_button'), 10, 1);
            add_filter('woocommerce_admin_field_krokedil_divider', array(__CLASS__, 'krokedil_divider'), 10, 1);
            add_filter('woocommerce_admin_field_krokedil_radio', array(__CLASS__, 'krokedil_radio'), 10, 1);
        }
    }
    /**
     * Output the layout.
     *
     * @return void
     */
    public function output() : void
    {
        $navigation = SettingsPage::get_instance()->navigation($this->id);
        wp_enqueue_style('krokedil-settings-page');
        wp_enqueue_script('krokedil-settings-page');
        ?>
		<?php 
        $this->output_header();
        ?>
		<?php 
        if ($navigation) {
            ?>
			<?php 
            $navigation->output();
            ?>
		<?php 
        }
        ?>
		<?php 
        \printf('<div id="krokedil_settings_%s" class="krokedil_settings_page%s">', esc_attr($this->id), esc_attr($this->styled_output ? ' styled' : ''));
        ?>
			<div class="krokedil_settings__wrapper">
				<?php 
        $this->output_subsection_safe($this->styled_output && $this->settings_navigation ? \true : \false);
        ?>
				<?php 
        $this->output_sidebar();
        ?>
			</div>
		</div>
		<?php 
    }
    /**
     * Output the subsection content with graceful error handling.
     *
     * Wraps the dynamic content rendering (which an extending class provides through
     * output_page_content()) in an output buffer and try/catch, so a fatal error or
     * exception thrown while rendering a gateway/shipping page cannot blank the whole
     * settings screen. On failure it renders fallback content and an admin notice.
     * Mirrors the behaviour of SettingsPage::output().
     *
     * When no 'fallback_content' is supplied, the form fields are re-rendered using
     * WooCommerce's default display, which sidesteps whatever custom rendering threw.
     *
     * Supported page args:
     *  - 'fallback_content': string|callable rendered when the content render throws.
     *                        Defaults to the WooCommerce field display when omitted.
     *  - 'error_notice':     string used for the error notice instead of the default.
     *
     * @param bool $show_settings_navigation Whether to display the settings navigation.
     *
     * @return void
     */
    protected function output_subsection_safe(bool $show_settings_navigation = \false) : void
    {
        $fallback_content = $this->args['fallback_content'] ?? null;
        $error_notice = $this->args['error_notice'] ?? '';
        $buffer_level = \ob_get_level();
        \ob_start();
        try {
            $this->output_subsection($show_settings_navigation);
            echo (string) \ob_get_clean();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Buffered output is generated by trusted renderers above.
        } catch (\Throwable $exception) {
            while (\ob_get_level() > $buffer_level) {
                \ob_end_clean();
            }
            do_action('krokedil_settings_page_render_error', $this->id, $exception, $this->args);
            $notice_message = !empty($error_notice) ? $error_notice : __('An error occurred while rendering this settings page.', 'krokedil-settings');
            $fallback_buffer_level = \ob_get_level();
            \ob_start();
            try {
                if (\is_callable($fallback_content)) {
                    \call_user_func($fallback_content);
                } elseif (\is_string($fallback_content) && '' !== $fallback_content) {
                    echo wp_kses_post($fallback_content);
                } else {
                    // No fallback was provided, so fall back to WooCommerce's default field display.
                    $this->output_default_fields();
                }
                echo (string) \ob_get_clean();
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Buffered output is generated by trusted renderers above.
            } catch (\Throwable $fallback_exception) {
                while (\ob_get_level() > $fallback_buffer_level) {
                    \ob_end_clean();
                }
                $notice_message .= ' ' . __('Additionally, an error occurred while rendering the fallback content.', 'krokedil-settings');
                do_action('krokedil_settings_page_fallback_render_error', $this->id, $fallback_exception, $this->args);
            }
            echo '<div class="notice notice-error"><p>' . esc_html($notice_message) . '</p></div>';
        }
    }
    /**
     * Output the page HTML.
     *
     * @return void
     */
    public function output_page_content() : void
    {
        $this->output_default_fields();
    }
    /**
     * Render the page's form fields using WooCommerce's default field display.
     *
     * Used both as the default page content and as the built-in fallback when a
     * custom renderer throws.
     *
     * @return void
     */
    protected function output_default_fields() : void
    {
        ?>
		<table class="form-table">
			<?php 
        woocommerce_admin_fields($this->form_fields);
        ?>
		</table>
		<?php 
    }
    /**
     * Get the HTML as a string for a Klarna Payments section start.
     *
     * @param string               $html The HTML to append the section start to.
     * @param string               $key The key for the section.
     * @param array<string, mixed> $section The arguments for the section.
     *
     * @return string
     */
    public static function krokedil_section_start_html(string $html, string $key, array $section) : string
    {
        \ob_start();
        $classes = !empty($section['class']) ? ' ' . $section['class'] : '';
        ?>
		</table>
		<?php 
        \printf('<div id="krokedil_section_%s" class="krokedil_settings__section%s">', esc_attr($key), esc_attr($classes));
        ?>
			<div class="krokedil_settings__section_header">
				<span class="krokedil_settings__section_toggle dashicons dashicons-arrow-down-alt2"></span>
				<h3 class="krokedil_settings__section_title">
					<?php 
        echo esc_html($section['title']);
        ?>
				</h3>
				<div class="krokedil_settings__section_description">
					<p><?php 
        echo esc_html($section['description'] ?? '');
        ?></p>
				</div>
			</div>

			<div class="krokedil_settings__section_content">
				<table class="form-table">
		<?php 
        return (string) \ob_get_clean();
    }
    /**
     * Get the HTML as a string for a Klarna Payments section end.
     *
     * @param string               $html The HTML to append the section end to.
     * @param string               $key The key for the section end.
     * @param array<string, mixed> $section The arguments for the section.
     *
     * @return string
     */
    public static function krokedil_section_end_html(string $html, string $key, array $section) : string
    {
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Comes from the filter signature.
        \ob_start();
        ?>
		</table>
			</div>
				</div>
		<?php 
        return (string) \ob_get_clean();
    }
    /**
     * Custom rendering for a button field type.
     *
     * @param string               $html The HTML for the field.
     * @param string               $key The key for the field.
     * @param array<string, mixed> $section The section for the field.
     *
     * @return string
     */
    public static function krokedil_button_html(string $html, string $key, array $section) : string
    {
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Comes from the filter signature.
        \ob_start();
        $classes = $section['class'] ?? '';
        ?>
		<tr valign="top">
			<?php 
        \printf('<td class="%s">', esc_attr($classes));
        ?>
				<?php 
        if (!empty($section['description'])) {
            \printf('<p>%s</p>', esc_html($section['description']));
        }
        $alignment = $section['alignment'] ?? '';
        \printf('<button class="krokedil_button button%s" type="button" id="%s">', !empty($alignment) ? ' align-' . esc_attr($alignment) : ' no-align', esc_attr('krokedil_button_' . ($section['id'] ?? $key)));
        ?>
					<?php 
        echo esc_html($section['title'] ?? 'No title');
        ?>
				</button>
			</td>
		</tr>
		<?php 
        return (string) \ob_get_clean();
    }
    /**
     * Custom rendering for a divider field type.
     *
     * @param string               $html The HTML for the field.
     * @param string               $key The key for the field.
     * @param array<string, mixed> $section The section for the field.
     *
     * @return string
     */
    public static function krokedil_divider_html(string $html, string $key, array $section) : string
    {
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Comes from the filter signature.
        \ob_start();
        ?>
		<?php 
        \printf('<tr valign="top" class="form-section form-section-%s-end">', esc_attr($section['id']));
        ?>
			<td colspan="2"><hr /></td>
		</tr>
		<?php 
        return (string) \ob_get_clean();
    }
    /**
     * Custom rendering for a radio field type.
     *
     * @param string               $html The HTML for the field.
     * @param string               $key The key for the field.
     * @param array<string, mixed> $section The section for the field.
     *
     * @return string
     */
    public static function krokedil_radio_html(string $html, string $key, array $section) : string
    {
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Comes from the filter signature.
        \ob_start();
        $options = $section['options'] ?? array();
        $default = $section['default'] ?? '';
        $classes = $section['class'] ?? '';
        ?>
		<tr valign="top">
			<?php 
        \printf('<td class="%s %s">', esc_attr($classes), !empty($section['alignment']) ? 'align-prev' : 'no-align');
        ?>
				<?php 
        foreach ($options as $option_key => $option) {
            ?>
					<label style="margin-right:20px;">
						<?php 
            \printf('<input type="radio" name="%s" value="%s" %s>', esc_attr($key), esc_attr($option['value']), checked($option['value'], $default, \false));
            ?>
						<?php 
            echo esc_html($option['title'] ?? '');
            ?>
					</label>
				<?php 
        }
        ?>
			</td>
		</tr>
		<?php 
        return (string) \ob_get_clean();
    }
    /**
     * Print the krokedil_section_start field html.
     *
     * @param array<string, mixed> $section The section arguments.
     *
     * @return void
     */
    public static function krokedil_section_start(array $section) : void
    {
        echo self::krokedil_section_start_html('', $section['id'], $section);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in krokedil_section_start_html().
    }
    /**
     * Print the krokedil_section_end field html.
     *
     * @param array<string, mixed> $section The section arguments.
     *
     * @return void
     */
    public static function krokedil_section_end(array $section) : void
    {
        echo self::krokedil_section_end_html('', $section['id'], $section);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in krokedil_section_end_html().
    }
    /**
     * Print the krokedil_button field html.
     *
     * @param array<string, mixed> $section The section arguments.
     *
     * @return void
     */
    public static function krokedil_button(array $section) : void
    {
        echo self::krokedil_button_html('', $section['id'], $section);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in krokedil_button_html().
    }
    /**
     * Print the krokedil_divider field html.
     *
     * @param array<string, mixed> $section The section arguments.
     *
     * @return void
     */
    public static function krokedil_divider(array $section) : void
    {
        echo self::krokedil_divider_html('', $section['id'], $section);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in krokedil_divider_html().
    }
    /**
     * Print the krokedil_radio field html.
     *
     * @param array<string, mixed> $section The section arguments.
     *
     * @return void
     */
    public static function krokedil_radio(array $section) : void
    {
        echo self::krokedil_radio_html('', $section['id'], $section);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in krokedil_radio_html().
    }
}
