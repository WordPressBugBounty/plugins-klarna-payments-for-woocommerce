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
     * @return void
     */
    public function output_subsection()
    {
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
    protected static function get_locale()
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
     * @param array $link The resource to output.
     *
     * @return string
     */
    protected static function get_link($link)
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
        return \sprintf('<a href="%s" class="%s" target="%s" data-title="%s">%s</a>', esc_url($href), esc_attr($link['class'] ?? ''), esc_attr($link['target'] ?? ''), esc_attr($text ?? ''), esc_html($text));
    }
    /**
     * Get the link text output.
     *
     * @param array $link_text The link text to output.
     *
     * @return string
     */
    protected static function get_link_text($link_text)
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
     * @param array $description Description to output.
     *
     * @return string
     */
    protected static function get_description($description)
    {
        return $description[self::get_locale()] ?? $description['en'] ?? '';
    }
    /**
     * Get an image based on the type, either a base64 encoded string or URL.
     *
     * @param array $image Image to output.
     *
     * @return string
     */
    protected static function get_image($image)
    {
        $src = $image['src'] ?? '';
        return \sprintf('<div style="background-image:url(\'%s\')"></div>', $src, $src);
    }
    /**
     * Get a text based on locale.
     *
     * @param array $text Text to output.
     *
     * @return string
     */
    protected static function get_text($text)
    {
        return $text[self::get_locale()] ?? $text['text']['en'] ?? '';
    }
}
