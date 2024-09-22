<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

\defined('ABSPATH') || exit;
/**
 * Navigation class to handle the navigation for the subsections of the settings page.
 */
class Navigation
{
    /**
     * The arguments for the page.
     *
     * @var array $args
     */
    protected $args = array();
    /**
     * Class constructor.
     *
     * @param array $args Arguments for the page.
     *
     * @return void
     */
    public function __construct($args)
    {
        $this->args = $args;
    }
    /**
     * Get the current subsection we are on.
     *
     * @return string
     */
    public function get_current_subsection()
    {
        // Verify the nonce.
        wp_verify_nonce('__nonce');
        $subsection = \filter_input(\INPUT_GET, 'subsection', \FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$subsection) {
            $subsection = 'general';
        }
        return $subsection;
    }
    /**
     * Output the Navigation HTML.
     *
     * @return void
     */
    public function output()
    {
        $current_subsection = $this->get_current_subsection();
        $extra_subsections = $this->args['extra_subsections'] ?? array();
        $tabs = array('general' => __('General', 'krokedil-settings'));
        if (!empty($this->args['support'] ?? array())) {
            $tabs['support'] = __('Support', 'krokedil-settings');
        }
        if (!empty($this->args['addons'] ?? array())) {
            $tabs['addons'] = __('Addons', 'krokedil-settings');
        }
        foreach ($extra_subsections as $key => $subsection) {
            $tabs[$key] = $subsection['name'] ?? $key;
        }
        ?>
		<h2 class="nav-tab-wrapper">
			<?php 
        foreach ($tabs as $tab => $label) {
            ?>
				<a href="<?php 
            echo esc_url(add_query_arg('subsection', $tab));
            ?>" class="nav-tab <?php 
            echo $current_subsection === $tab ? 'nav-tab-active' : '';
            ?>"><?php 
            echo esc_html($label);
            ?></a>
			<?php 
        }
        ?>
		</h2>
		<?php 
    }
}
