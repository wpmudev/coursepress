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

	function setAttributes( $attr = array() ) {
		if ( ! $attr )
			return '';

		$vars = array();

		foreach ( $attr as $key => $value ) {
			$vars[] = $key . '="' . $value . '""';
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