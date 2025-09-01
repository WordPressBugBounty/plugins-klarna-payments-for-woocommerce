<?php

namespace KrokedilKlarnaPaymentsDeps;

if (!\defined('ABSPATH')) {
    exit;
}
$report = get_option('krokedil_support_' . $id);
$settings_title = "{$name} plugin settings";
?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="6" data-export-label="<?php 
esc_attr($name) . ' request log';
?>">
				<h2><?php 
echo esc_html($name) . ' request log';
?></h2>
			</th>
		</tr>
		<tr>
			<td><strong><?php 
esc_html_e('Timestamp', 'krokedil-support');
?></strong></td>
			<td class="help"></td>
			<td><strong><?php 
esc_html_e('Code', 'krokedil-support');
?></strong></td>
			<td colspan="6"><strong><?php 
esc_html_e('Message', 'krokedil-support');
?></strong></td>
		</tr>
	</thead>
	<tbody>
		<?php 
if (!empty($report)) {
    ?>
			<?php 
    $report = \array_reverse(\json_decode($report, \true));
    ?>
			<?php 
    foreach ($report as $log) {
        ?>
				<tr>
					<td><?php 
        echo esc_html($log['timestamp']);
        ?></td>
					<td class="help"></td>
					<td><?php 
        echo esc_html($log['response']['code']);
        ?></td>
					<td><?php 
        echo esc_html(\trim($log['response']['message'], '"'));
        ?></td>
					<td><?php 
        echo esc_html(\trim(empty($log['response']['extra']) ? '' : wp_json_encode($log['response']['extra']), '"'));
        ?></td>
				</tr>
			<?php 
    }
    ?>
		<?php 
} else {
    ?>
			<tr>
				<td colspan="6" data-export-label="No errors"><?php 
    esc_html_e('No error logs', 'krokedil-support');
    ?></td>
			</tr>
		<?php 
}
?>
	</tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="6" data-export-label="<?php 
echo esc_attr($settings_title);
?>">
				<h2><?php 
echo esc_html($settings_title);
?></h2>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php 
if (!empty($settings)) {
    ?>
			<?php 
    foreach ($settings as $setting) {
        ?>
				<tr>
					<?php 
        if ($setting['type'] === 'section') {
            ?>
						<td colspan="6" data-export-label="<?php 
            echo esc_attr($setting['title']);
            ?>">
							<h2><?php 
            echo esc_html($setting['title']);
            ?></h2>
						</td>
					<?php 
        } else {
            ?>
						<td><?php 
            echo esc_html($setting['title']);
            ?></td>
						<td class="help"></td>
						<td><?php 
            echo esc_html('password' === $setting['type'] ? '[REDACTED]' : $setting['value']);
            ?></td>
					<?php 
        }
        ?>
				</tr>
			<?php 
    }
    ?>
		<?php 
} else {
    ?>
			<tr>
				<td colspan="6" data-export-label="No errors"><?php 
    esc_html_e('Settings could not be retrieved.', 'krokedil-support');
    ?></td>
			</tr>
		<?php 
}
?>
	</tbody>
</table>
<?php 
