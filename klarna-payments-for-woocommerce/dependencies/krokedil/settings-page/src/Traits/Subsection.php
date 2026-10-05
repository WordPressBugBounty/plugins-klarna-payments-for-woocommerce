<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits;

trait Subsection
{
    /**
     * Title of the Subsection.
     *
     * @var string
     */
    protected $title = '';
    /**
     * Locale of the site.
     *
     * @var string
     */
    protected static $locale = '';
    /**
     * Output the Subsection.
     *
     * @param bool $show_settings_navigation Whether to display the settings navigation.
     * @return void
     */
    public function output_subsection(bool $show_settings_navigation = \false) : void
    {
        $settings = \array_filter($this->form_fields, function ($field) {
            return isset($field['type']) && 'krokedil_section_start' === $field['type'];
        });
        if ($show_settings_navigation) {
            ?>
			<div class="krokedil_settings__settings_navigation">
				<h3><?php 
            esc_html_e('Sections', 'krokedil-settings');
            ?></h3>
					<?php 
            foreach ($settings as $setting) {
                ?>
						<p>
							<span>
								&raquo;
								<a href="#<?php 
                echo esc_attr($setting['id']);
                ?>" class="krokedil_settings__settings_navigation_link">
									<?php 
                echo esc_html($setting['title'] ?? $setting['name'] ?? '');
                ?>
								</a>
							</span>
						</p>
						<?php 
            }
            ?>
			</div>
			<?php 
        }
        ?>
		<div class="krokedil_settings__content">
		<?php 
        $this->output_page_content();
        ?>
		</div>
		<?php 
    }
    /**
     * Get the locale of the site.
     *
     * @return string
     */
    protected static function get_locale() : string
    {
        if (!empty(self::$locale)) {
            return self::$locale;
        }
        $locale = get_locale();
        $locale = \strtolower(\substr($locale, 0, 2));
        self::$locale = $locale;
        return $locale;
    }
    /**
     * Get the sidebar link output.
     *
     * @param array<string, mixed> $link The resource to output.
     *
     * @return string
     */
    protected static function get_link(array $link) : string
    {
        $href = $link['href'] ?? '';
        $text = $link['text'] ?? '';
        if (\is_array($href)) {
            $href = $link['href'][self::get_locale()] ?? $link['href']['en'] ?? '';
        }
        if (\is_array($text)) {
            $text = $text[self::get_locale()] ?? $text['en'] ?? '';
        }
        // If href is empty, return.
        if (empty($href)) {
            return '';
        }
        return \sprintf('<a href="%s" class="%s" target="%s" data-title="%s">%s</a>', esc_url($href), esc_attr($link['class'] ?? ''), esc_attr($link['target'] ?? ''), esc_attr($text), esc_html($text));
    }
    /**
     * Get the link text output.
     *
     * @param array<string, mixed> $link_text The link text to output.
     *
     * @return string
     */
    protected static function get_link_text(array $link_text) : string
    {
        $link = $link_text['link'] ?? array();
        $text = $link_text['text'][self::get_locale()] ?? $link_text['text']['en'] ?? '';
        // If text or link is empty, return.
        if (empty($text)) {
            return '';
        }
        return \sprintf('<p>%s</p>', \sprintf($text, self::get_link($link)));
    }
    /**
     * Get a description based on locale.
     *
     * @param array<string, string> $description Description to output.
     *
     * @return string
     */
    protected static function get_description(array $description) : string
    {
        return $description[self::get_locale()] ?? $description['en'] ?? '';
    }
    /**
     * Get an image based on the type, either a base64 encoded string or URL.
     *
     * @param array{src?: string} $image Image to output.
     *
     * @return string
     */
    protected static function get_image(array $image) : string
    {
        $src = $image['src'] ?? '';
        return \sprintf('<div style="background-image:url(\'%s\')"></div>', $src);
    }
    /**
     * Get a text based on locale.
     *
     * @param array<string, string>|string $text Text to output.
     *
     * @return string
     */
    protected static function get_text($text) : string
    {
        if (\is_string($text)) {
            return $text;
        }
        return $text[self::get_locale()] ?? $text['en'] ?? '';
    }
    /**
     * Print the content.
     *
     * @param array<string, mixed> $item The item to print.
     * @param bool                 $ignore_p_tag Whether to ignore the p tag.
     *
     * @return string
     */
    public static function print_content(array $item, bool $ignore_p_tag = \false) : string
    {
        $type = $item['type'];
        $text = '';
        switch ($type) {
            case 'text':
                $text = self::get_text($item);
                break;
            case 'link':
                $text = self::get_link($item);
                break;
            case 'link_text':
                $text = self::get_link_text($item);
                break;
            case 'list':
                $list_items = $item['items'];
                $list = '<ul class="krokedil_settings__list">';
                foreach ($list_items as $list_item) {
                    $list .= '<li>';
                    $list .= self::print_content($list_item, \true);
                    $list .= '</li>';
                }
                $list .= '</ul>';
                $text = $list;
                break;
            case 'spacer':
                $ignore_p_tag = \true;
                $text = '<div class="krokedil_settings__spacer"></div>';
                break;
            default:
                return '';
        }
        if ($ignore_p_tag) {
            return $text;
        }
        return '<p>' . $text . '</p>';
    }
}
