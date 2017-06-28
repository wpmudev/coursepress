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

    function setUp( $args ) {
        if ( ! empty( $args ) ) {
            foreach ( $args as $key => $value ) {
                $this->__set( $key, $value );
            }
        }
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
            $vars[] = $key . '="' . esc_attr( $value ) . '"';
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

    function to_array( $array ) {
        if ( is_object( $array ) ) {
            $array = get_object_vars( $array );
        }

        if ( is_array( $array ) && ! empty( $array ) ) {
            foreach ( $array as $key => $value ) {
                $array[ $key ] = $this->to_array( $value );
            }
        }

        return $array;
    }

    /**
     * Check if current install is PRO or FREE
     */
    function is_pro() {
    }

    /**
     * Replaces the defined placeholders in the content with specified values.
     *
     * @since  2.0.0
     * @param  string $content The full content, with placeholders.
     * @param  array  $vars List of placeholder => value.
     * @return string The content but with all placeholders replaced.
     */
    function replace_vars( $content, $vars ) {
        $keys = array();
        $values = array();

        foreach ( $vars as $key => $value ) {
            $keys[] = $key;
            $values[] = $value;
        }

        return str_replace( $keys, $values, $content );
    }
}