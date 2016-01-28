<?php

/*
Plugin Name: Next Post Type Link
Plugin URI: https://github.com/benhuson/next-post-type-link
Description: Next/Previous links for custom post types (currently only supports pages).
Version: 0.2
Author: Ben Huson
Author URI: http://www.benhuson.co.uk
License: GPL2
*/

if ( ! function_exists( 'next_post_type_link' ) ) {
	function next_post_type_link( $args = null ) {
		return adjacent_post_type_link( $args, false );
	}
}

if ( ! function_exists( 'previous_post_type_link' ) ) {
	function previous_post_type_link( $args = null ) {
		return adjacent_post_type_link( $args );
	}
}

if ( ! function_exists( 'adjacent_post_type_link' ) ) {
	function adjacent_post_type_link( $args = null, $previous = true ) {
		global $post;
		
		$default_format = $previous ? '&laquo; %link' : '%link &raquo;';
		$args = wp_parse_args( $args, array(
			'format'    => $default_format,
			'link'      => '%title',
			'echo'      => true,
			'query'     => array()
		) );

		$query_args = wp_parse_args( $args['query'], array(
			'posts_per_page' => -1,
			'post_type'      => get_post_type( $post ),
			'orderby'        => 'menu_order',
			'order'          => 'asc',
			'post_parent'    => $post->post_parent
		) );

		$pagelist = get_posts( $query_args );

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

				if ( $args['echo'] ) {
					echo $output;
				} else {
					return $output;
				}

			}
		}
	}
}
