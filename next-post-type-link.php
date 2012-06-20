<?php

/*
Plugin Name: Next Post Type Link
Plugin URI: http://wordpress.org/extend/plugins/
Description: Next/Previous links for custom post types (currently only supports pages).
Version: 0.1
Author: Ben Huson
Author URI: http://www.benhuson.co.uk
License: GPL2
*/

if ( ! function_exists( 'next_post_type_link' ) ) {
	function next_post_type_link( $args = null ) {
		adjacent_post_type_link( $args, false );
	}
}

if ( ! function_exists( 'previous_post_type_link' ) ) {
	function previous_post_type_link( $args = null ) {
		adjacent_post_type_link( $args );
	}
}

if ( ! function_exists( 'adjacent_post_type_link' ) ) {
	function adjacent_post_type_link( $args = null, $previous = true ) {
		global $post;
		
		$default_format = $previous ? '&laquo; %link' : '%link &raquo;';
		$args = wp_parse_args( $args, array(
			'format' => $default_format,
			'link'   => '%title',
			'echo'   => true
		) );
		
		$pagelist = get_posts( array(
			'numberposts' => -1,
			'post_type'   => 'page',
			'sort_column' => 'menu_order',
			'sort_order'  => 'asc',
			'post_parent' => $post->post_parent
		) );
		$pages = array();
		foreach ( $pagelist as $page ) {
			$pages[] = $page->ID;
		}
		$current = array_search( $post->ID, $pages );
		if ( ( $previous && ! isset( $pages[$current - 1] ) ) || ( ! $previous && ! isset( $pages[$current + 1] ) ) ) {
			return;
		}
		$adjacent_id = $previous ? $pages[$current - 1] : $pages[$current + 1];
		foreach ( $pagelist as $page ) {
			if ( $adjacent_id == $page->ID ) {
				$title = $page->post_title;
			
				if ( empty( $page->post_title ) )
					$title = $previous ? __( 'Previous Page' ) : __( 'Next Page' );
			
				$title = apply_filters( 'the_title', $title, $page->ID );
				$rel = $previous ? 'prev' : 'next';
				
				$string = '<a href="' . get_permalink( $page ) . '" rel="' . $rel . '">';
				$args['link'] = str_replace( '%title', $title, $args['link'] );
				$args['link'] = $string . $args['link'] . '</a>';
			
				$args['format'] = str_replace( '%link', $args['link'], $args['format'] );
			
				$adjacent = $previous ? 'previous' : 'next';
				$output = apply_filters( "{$adjacent}_page_link", $args['format'], $args['link'] );
				
				if ( $args['echo'] )
					echo $output;
				return $output;
			}
		}
	}
}
