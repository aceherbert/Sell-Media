<?php

/**
 * Search Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaSearch {

	private $query_instance;

	/**
	 * Init
	 */
	public function __construct(){
		add_filter( 'posts_join', array( &$this, 'terms_join' ) );
		add_filter( 'posts_where', array( &$this, 'tax_search_where' ) );
		add_filter( 'posts_groupby', array( &$this, 'tax_search_groupby' ) );
		add_filter( 'the_permalink', array( &$this, 'search_permalinks' ) );
	}


	/**
	 * Join for searching tags
	 *
	 * @since 1.8.7
	 */
	public function terms_join( $join ) {
		global $wpdb;

		if ( is_search() ) {
			$join .= "
				INNER JOIN
				{$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
				INNER JOIN
				{$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
				INNER JOIN
				{$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
			";
		}
		return $join;
	}

	/**
	 * Find the search term in the taxonomy 'keyword'
	 * @param  $where
	 * @return $where
	 */
	public function tax_search_where( $where ) {
		global $wpdb;

		if ( is_search() ) {
			// add the search term to the query
			$where .= " OR ({$wpdb->term_taxonomy}.taxonomy LIKE 'keywords' AND {$wpdb->terms}.name LIKE ('%".$wpdb->escape( get_query_var('s') )."%')) ";
		}

		return $where;
	}

	/**
	 * Group the results by post id to avoid duplicate results because of the join
	 * @param  $groupby
	 * @return $groupby
	 */
	public function tax_search_groupby( $groupby ) {
		global $wpdb;

		if ( is_search() ) {
			$groupby = "{$wpdb->posts}.ID";
		}

		return $groupby;
	}


	/**
	 * Alter permalink for attachment page
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function search_permalinks( $url ) {
		global $post;

		if ( is_search() && 'attachment' == get_post_type( $post->ID ) ) {
			$url = add_query_arg( 'foo', 'bar', $url );
		}
		return $url;
	}


	/**
	 * Search form
	 *
	 * @since 1.8.7
	 */
	public function form( $url=null, $used=null ){

		$settings = sell_media_get_plugin_options();

		// only use this method if it hasn't already been used on the page
		static $used;
		if ( ! isset( $used ) ) {
			$used = true;

			$query = ( get_search_query() ) ? get_search_query() : '';

			$html = '';
			$html .= '<div class="sell-media-search">';
			$html .= '<form role="search" method="get" id="sell-media-search-form" class="sell-media-search-form" action="' . site_url() . '">';
			$html .= '<div class="sell-media-search-inner cf">';

			// Visible search options wrapper
			$html .= '<div id="sell-media-search-visible" class="sell-media-search-visible cf">';

			// Input field
			$html .= '<div id="sell-media-search-query" class="sell-media-search-field sell-media-search-query">';
			$html .= '<input type="text" value="' . $query . '" name="s" id="sell-media-search-text" class="sell-media-search-text" placeholder="' . apply_filters( 'sell_media_search_placeholder', sprintf( __( 'Search for %1$s', 'sell_media' ), empty( $settings->post_type_slug ) ? 'items' : $settings->post_type_slug ) ) . '"/>';
			$html .= '</div>';

			// Submit button
			$html .= '<div id="sell-media-search-submit" class="sell-media-search-field sell-media-search-submit">';
			$html .= '<input type="hidden" name="post_type[]" value="sell_media_item" />';
			$html .= '<input type="hidden" name="post_type[]" value="attachment" />';
			$html .= '<input type="submit" id="sell-media-search-submit-button" class="sell-media-search-submit-button" value="' . apply_filters( 'sell_media_search_button', __( 'Search', 'sell_media' ) ) . '" />';
			$html .= '</div>';

			$html .= '</div>';

			// Hidden search options wrapper
			$html .= '<div id="sell-media-search-hidden" class="sell-media-search-hidden cf">';

			// Exact match field
			$html .= '<div id="sell-media-search-exact-match" class="sell-media-search-field sell-media-search-exact-match">';
			$html .= '<label for="sentence" id="sell-media-search-exact-match-desc" class="sell-media-search-exact-match-desc sell-media-tooltip" data-tooltip="Check to limit search results to exact phrase matches. Without exact phrase match checked, a search for \'New York Yankees\' would return results containing any of the three words \'New\', \'York\' and \'Yankees\'.">' . __( 'Exact phrase match (?)', 'sell_media' ) . '</label>';
			$html .= '<input type="checkbox" value="1" name="sentence" id="sentence" />';
			$html .= '</div>';

			// Collection field
			$html .= '<div id="sell-media-search-collection" class="sell-media-search-field sell-media-search-collection">';
			$html .= '<label for="collection">' . __( 'Collection', 'sell_media' ) . '</label>';
			$html .= '<select name="collection">';
			$html .= '<option value="">' . esc_attr( __( 'All', 'sell_media' ) ) . '</option>';

			$categories = get_categories( 'taxonomy=collection' );
			foreach ( $categories as $category ) {
				$html .= '<option value="' . $category->category_nicename . '">';
				$html .= $category->cat_name;
				$html .= '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';

			// Hidden search options wrapper
			$html .= '</div>';

			// Close button
			$html .= '<a href="javascript:void(0);" class="sell-media-search-close">&times;</a>';

			$html .= '</div>';
			$html .= '</form>';
			$html .= '</div>';

			echo apply_filters( 'sell_media_searchform_filter', $html );
		}
	}

}
