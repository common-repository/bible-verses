<?php
/**
 * Plugin Name:     Bible Verses - Random Bible Verses
 * Plugin URI:      https://duckdev.com/products/
 * Description:     Show random Bible verses on your website as widget or using a shortcode.
 * Version:         2.0.0
 * Author:          Joel James
 * Author URI:      https://duckdev.com/
 * Donate link:     https://paypal.me/JoelCJ
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:     bible-verses
 * Domain Path:     /languages
 *
 * Bible Verses is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Bible Verses is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Bible Verses. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author   Joel James <me@joelsays.com>
 * @license  http://www.gnu.org/licenses/ GNU General Public License
 * @category Main
 * @link     https://duckdev.com/products/
 * @package  Plugin
 */

/**
 * Class Bible_Verses
 *
 * @since 2.0.0
 */
class DuckDev_Bible_Verses extends WP_Widget {

	/**
	 * Bible_Verses constructor.
	 *
	 * Setup widget and register it.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct(
			'duckdev-bible-verses',
			__( 'Bible Verses', 'bible-verses' ),
			array(
				'classname'   => 'duckdev-bible-verses',
				'description' => __( 'Shows good bible verses randomly on each refresh!', 'bible-verses' ),
			)
		);

		// Run widget.
		add_action( 'widgets_init', array( $this, 'init' ) );

		// Add styles.
		add_action( 'wp_head', array( $this, 'styles' ) );

		// Register shortcodes.
		add_shortcode( 'bible-verse', array( $this, 'render_verse' ) );
		add_shortcode( 'js-bible-verses', array( $this, 'render_verse' ) ); // Deprecated.
	}

	/**
	 * Register our widget with WordPress and init.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Register widget to WP.
		register_widget( 'DuckDev_Bible_Verses' );
	}


	/**
	 * Add inline styles for the widget.
	 *
	 * We are not loading an extra file for small css.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function styles() {
		// Only if widget is active.
		if ( is_active_widget( false, false, 'duckdev-bible-verses' ) ) {
			?>
			<style>
				.dailyVerses.bibleVerse {
					font-weight: bold;
					padding-top: 10px !important;
				}
			</style>
			<?php
		}
	}

	/**
	 * Echoes the widget content.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore

		// Show title.
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; // phpcs:ignore
		}

		// Display the content.
		echo $this->render_verse(); // phpcs:ignore

		echo $args['after_widget']; // phpcs:ignore
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function form( $instance ) {
		// Get instance.
		$instance = wp_parse_args(
			(array) $instance,
			array( 'title' => __( 'Bible Verse', 'bible-verses' ) )
		);
		// Field ID.
		$field_id = $this->get_field_id( 'title' );
		// Title.
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>">
				<?php esc_html_e( 'Title:', 'bible-verses' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @since 2.0.0
	 *
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		// Set title.
		$instance['title'] = empty( $new_instance['title'] ) ? '' : wp_strip_all_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @since 2.0.0
	 *
	 * @return string HTML output of verse content.
	 */
	public function render_verse() {
		// Get verse content.
		$verse = $this->get_verse();

		// Make sure it's not empty.
		if ( empty( $verse ) ) {
			$verse = '<div class="dailyVerses bibleText">For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.</div><div class="dailyVerses bibleVerse">John 3:16</div>';
		}

		return $verse;
	}

	/**
	 * Get verse content for the position.
	 *
	 * First check the cache. If not found in cache,
	 * make API request and get it from the API.
	 * Thanks to https://dailyverses.net/website
	 *
	 * @since 2.0.0
	 *
	 * @return string Verse html.
	 */
	private function get_verse() {
		// Get a random number within 200.
		$position = wp_rand( 0, 200 );

		// Get from cache.
		$verse = get_transient( 'duckdev_bible_verse_' . $position );

		// If not found in cache, get from API.
		if ( empty( $verse ) ) {
			// API URL.
			$url = 'http://dailyverses.net/getrandomverse.ashx?language=en&type=random1_6&position=' . $position;
			// Make remote request and get verse.
			$result = wp_remote_get( $url );

			if ( ! is_wp_error( $result ) ) {
				$body = wp_remote_retrieve_body( $result );
				if ( ! empty( $body ) ) {
					// Replace required strings.
					$verse = str_replace( ',', '&#44;', $body );

					// Update to cache.
					set_transient( 'duckdev_bible_verse_' . $position, $verse, 3600 );
				}
			}
		}

		/**
		 * Filter hook to modify bible verse.
		 *
		 * @param string $verse    Verse.
		 * @param int    $position Random position.
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'bible_verses_verse', $verse, $position );
	}
}

/**
 * Create new instance of the plugin.
 *
 * This will boot the plugin.
 *
 * @since 2.0.0
 *
 * @return DuckDev_Bible_Verses
 */
function duckdev_bible_verses() {
	static $instance = null;

	// If instance is not created yet.
	if ( ! $instance instanceof DuckDev_Bible_Verses ) {
		$instance = new DuckDev_Bible_Verses();
	}

	return $instance;
}

// Run plugin.
add_action( 'plugins_loaded', 'duckdev_bible_verses' );
