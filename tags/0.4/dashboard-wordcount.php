<?php
/**
 * Plugin Name: Dashboard Wordcount
 * Plugin URI: 
 * Description: Updates the Dashboard's At a Glance widget to show the total word count of all the published posts in this Wordpress website (and average word count per post). Also shows the age of the website (time since the oldest post). Uses the default dashboard icons and styling, so it's completely seamless. Just more information for you.
 * Version: 0.4
 * Author: Ricardo Jorge
 * Author URI: http://www.ricardojorge.net/
 * License: GPL2
 */
 
/*  Copyright 2014 Ricardo Jorge Pinto  (email : ricardjorg@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	function post_word_count() {
		$count = 0;
		$posts = get_posts( array(
			'numberposts' => -1,
			'post_type' => array( 'post', 'page' )
		));
		foreach( $posts as $post ) {
			$count += str_word_count( strip_tags( get_post_field( 'post_content', $post->ID )));
		}
		$num =  number_format_i18n( $count );
		// This block will add your word count to the stats portion of the Right Now box
		$text = _n( 'Word', 'Words', $num );
		$average = $count;
		$average /= count($posts);
		$average = number_format_i18n( $average );
		$url = admin_url( 'edit.php' );
		echo "<style scoped>.word-count a:before { content:'\\f122' !important; }</style><li class='word-count'><a href='{$url}' title='Average of {$average} words per post'>{$num} {$text}</a></li>";
	}

	// add to Content Stats table
	add_action('dashboard_glance_items', 'post_word_count');

	function post_year_count() {
		$oldest = dbwc_first_post_date();
		$diff = dbwc_first_post_date_diff();

		$url = admin_url( 'edit.php' );
		echo "<style scoped>.year-count a:before {content:'\\f118' !important;}</style><li class='year-count' ><a href='{$url}' title='Since {$oldest}'>{$diff}</a></li>";
	}

	// add to Content Stats table
	add_action( 'dashboard_glance_items', 'post_year_count');
	
	function post_comment_word_count() {
		$current = number_format_i18n( dbwc_current_user_comment_word_count() );
		$total = number_format_i18n( dbwc_all_users_comment_word_count() );
		$others = number_format_i18n( $total - $current );
		$url = admin_url( 'edit-comments.php' );
		echo "<style scoped>.comment-word-count a:before { content:'\\f473' !important; }</style><li class='comment-word-count'><a href='{$url}' title='{$current} words in comments written by you and {$others} words in comments by other users'>{$total} words in comments</a></li>";
	}

	// add to Content Stats table
	add_action( 'dashboard_glance_items', 'post_comment_word_count');

	/**
	* Get First Post Date Function
	*
	* @return Date of first post
	*/
	function dbwc_first_post_date() {
		$ax_args = array(
		'numberposts' => -1,
		'post_status' => 'publish',
		'order' => 'ASC'
		);

		// Get all posts in order of first to last
		$ax_get_all = get_posts($ax_args);

		// Extract first post from array
		$ax_first_post = $ax_get_all[0];

		// Assign first post date to var
		$ax_first_post_date = $ax_first_post->post_date;
		
		// return date in required format
		$output = date(get_option('date_format'), strtotime($ax_first_post_date));

		return $output;
	}

	function dbwc_first_post_date_diff() {
		$ax_args = array(
		'numberposts' => -1,
		'post_status' => 'publish',
		'order' => 'ASC'
		);

		// Get all posts in order of first to last
		$ax_get_all = get_posts($ax_args);

		// Extract first post from array
		$ax_first_post = $ax_get_all[0];
		
		// return date in required format
		$output = human_time_diff(get_the_time('U',$ax_first_post->ID), current_time('timestamp'));

		return $output;
	}
	
	function dbwc_current_user_comment_word_count() {
		$count = 0;
		$user_id = get_current_user_id();
		$comments = get_comments( array(
			'user_id' => $user_id
		));
		foreach( $comments as $comment ) {
			$count += str_word_count( $comment->comment_content );
		}
		return $count;
	}
	
	function dbwc_all_users_comment_word_count() {
		$count = 0;
		$comments = get_comments();
		foreach( $comments as $comment ) {
			$count += str_word_count( $comment->comment_content );
		}
		return $count;
	}
?>