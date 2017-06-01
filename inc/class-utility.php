<?php
/**
 * Class CoursePress_Utility
 *
 * @since 3.0
 * @package CoursePress
 */
abstract class CoursePress_Utility {
	function __set( $name, $value ) {
		$this->{$name} = $value;
	}

	function __get( $name ) {
		if ( isset( $this->{$name} ) )
			return $this->{$name};

		return null;
	}

	function date_time_now() {
		$time_now = current_time( 'timestamp' );
		$date_now = date( 'M/d/y', current_time( 'timestamp' ) );

		// Time now is not the current time but rather the timestamp of the starting date today (00:01).
		$time_now = strtotime( $date_now, $time_now );

		return $time_now;
	}

	function setAttributes( $attr = array() ) {
		if ( ! $attr )
			return '';

		$vars = array();

		foreach ( $attr as $key => $value ) {
			$vars[] = $key . '="' . $value . '"';
		}

		return implode( ' ', $vars );
	}

	function create_html( $tag, $attributes = array(), $content = '' ) {
		$html = '<' . $tag;

		if ( ! empty( $attributes ) )
			$html .= ' ' . $this->setAttributes( $attributes );

		$single_tags = array( 'img', 'input' );

		if ( in_array( $tag, $single_tags ) )
			$html .= ' />';
		else
			$html .= '>' . $content . '</' . $tag . '>';

		return $html;
	}
}