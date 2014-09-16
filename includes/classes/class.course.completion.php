<?php

if ( !defined('ABSPATH') )
    exit; // Exit if accessed directly

if ( !class_exists('Course_Completion') ) {

    class Course_Completion extends Course {

        /**
         * Primary object array for determining completion.
         *
         * The following items get added during constructions:  
         *  
         * * ->modules()  
         * * ->page_count  
         * * ->input_module_ids[]  
         * * ->mandatory_module_ids[]  
         * * ->gradable_module_ids[]  
         *
         * The following additional items get added when initialising student status:  
         * 
         * * ->all_pages_viewed // bool  
         * * ->pages_visited[]  
         * * ->mandatory_answered[]  
         * * ->all_mandatory_answered // bool  
         * * ->gradable_passed[]  
         * * ->all_modules_passed //bool  
         * * ->remaining_mandatory_items
         * * ->total_steps  
         * * ->completed_steps  
         * * ->completion  
         *
         * @since 1.0
         */
        var $units = array();
        var $unit_index = array();
        var $completion_status = 'unfinished';

        function __construct( $id = '', $output = 'OBJECT' ) {
            parent::__construct($id, $output);
            $units = $this->get_units();
            foreach ( $units as $key => $unit ) {
                $this->unit_index[$unit->ID] = $key;
                // Used to get input modules
                $unit->modules = $this->get_unit_modules($unit->ID);
                // Used to determine page views
                $unit->page_count = $this->get_unit_pages($unit->modules);
                // Used to determine mandatory modules
                $unit->input_module_ids = $this->get_input_modules($unit->modules);
                $unit->mandatory_module_ids = $this->get_mandatory_modules($unit->modules, $unit->input_module_ids);
                // Uses only mandatory modules
                $unit->gradable_module_ids = $this->get_gradable_modules($unit->modules, $unit->input_module_ids);
				$this->units[] = $unit;
            }
        }

        function Course_Completion( $id = '', $output = 'OBJECT' ) {
            $this->__construct($id, $output);
        }

        function get_unit_modules( $unit_id ) {
            $module = new Unit_Module();
            $modules = $module->get_modules($unit_id);
            return $modules;
        }

        function get_unit_pages( $modules ) {
            $pages_num = 1;
            foreach ( $modules as $mod ) {
                $class_name = $mod->module_type;
                if ( 'page_break_module' == $class_name ) {
                    $pages_num++;
                }
            }
            return $pages_num;
        }

        function get_input_modules( $modules ) {
            $inputs = array();
            $input_modules = array( 'checkbox_input_module', 'file_input_module', 'radio_input_module', 'text_input_module' );
            $count = 0;
            foreach ( $modules as $mod ) {
                $class_name = $mod->module_type;
                if ( in_array($class_name, $input_modules) ) {
                    $inputs[$count] = $count;
                }
                $count += 1;
            }
            return $inputs;
        }

        function get_mandatory_modules( $modules, $input_ids ) {
            $mandatory_ids = array();
            foreach ( $input_ids as $input_id ) {
                $mandatory = get_post_meta($modules[$input_id]->ID, 'mandatory_answer', true);
                if ( 'yes' == $mandatory ) {
                    $mandatory_ids[$input_id] = $input_id;
                }
            }
            return $mandatory_ids;
        }

        function get_gradable_modules( $modules, $input_ids ) {
            $gradable_ids = array();
            foreach ( $input_ids as $input_id ) {
                $gradable = get_post_meta($modules[$input_id]->ID, 'gradable_answer', true);
                if ( 'yes' == $gradable ) {
                    $gradable_ids[$input_id] = $input_id;
                }
            }
            return $gradable_ids;
        }

        function init_pages_visited( $student_id = 0 ) {

            foreach ( $this->units as $unit ) {
                $pages = get_user_meta($student_id, 'visited_unit_pages_' . $unit->ID . '_page', true);
                $pages = explode(',', $pages);
                unset($pages[0]);
                $unit->pages_visited = $pages;
            }
        }

        function check_pages_visited( $student_id = 0 ) {
            foreach ( $this->units as $unit ) {
                $visited = $unit->pages_visited;
                if ( $unit->page_count == count($visited) ) {
                    $unit->all_pages_viewed = true;
                } else {
                    $unit->all_pages_viewed = false;
                }
            }
        }

        function init_mandatory_modules_answered( $student_id = 0 ) {

            foreach ( $this->units as $unit ) {
                $unit->mandatory_answered = array();
                foreach ( $unit->mandatory_module_ids as $mod_id ) {
                    $module = $unit->modules[$mod_id];
                    $module = new $module->module_type($module->ID);
                    $response = $module->get_response($student_id, $unit->modules[$mod_id]->ID);
                    if ( !empty($response) ) {
                        $unit->mandatory_answered[$mod_id] = true;
                    } else {
                        $unit->mandatory_answered[$mod_id] = false;
                    }
                }
            }
        }

        function check_mandatory_modules_answered( $student_id = 0 ) {

            foreach ( $this->units as $unit ) {
                $unit_answered = true;
                foreach ( $unit->mandatory_module_ids as $mod_id ) {
                    $module = $unit->modules[$mod_id];
                    $answered = false;
                    if ( !empty($unit->mandatory_answered[$mod_id]) && $unit->mandatory_answered[$mod_id] ) {
                        $answered = true;
                    }
                    $unit_answered &= $answered;
                }
                $unit->all_mandatory_answered = $unit_answered;
            }
        }

        function init_gradable_modules_passed( $student_id = 0 ) {
            foreach ( $this->units as $unit ) {
                $unit->gradable_passed = array();
                foreach ( $unit->gradable_module_ids as $mod_id ) {
                    $module = $unit->modules[$mod_id];
                    $module = new $module->module_type($module->ID);
                    $response = $module->get_response($student_id, $unit->modules[$mod_id]->ID);
                    $minimum_grade = get_post_meta($unit->modules[$mod_id]->ID, 'minimum_grade_required', true);
                    $grade = false;
                    $success = false;
                    if ( !empty($response) ) {
                        $unit_module = new Unit_Module();
                        $grade = $unit_module->get_response_grade($response->ID);
                        $success = $grade['grade'] >= $minimum_grade ? true : false;
                    }

                    $unit->gradable_passed[$mod_id] = $success;
                }
            }
        }

        function check_gradable_modules_passed( $student_id = 0 ) {

            foreach ( $this->units as $unit ) {
                $unit_passed = true;
				$unit->gradable_passed_count = 0;
				$unit->total_gradable = count( $unit->gradable_module_ids );
                foreach ( $unit->gradable_module_ids as $mod_id ) {
                    $module = $unit->modules[$mod_id];
                    $success = false;
                    if ( !empty($unit->gradable_passed[$mod_id]) && $unit->gradable_passed[$mod_id] ) {
                        $success = true;
						$unit->gradable_passed_count += 1;
                    }
                    $unit_passed &= $success;
                }
                $unit->all_modules_passed = $unit_passed;
            }
        }

        function get_remaining_mandatory_items() {
            foreach ( $this->units as $unit ) {

                $mandatory = $unit->mandatory_module_ids;
                $answered = array_filter($unit->mandatory_answered);

                // We only want mandatory items, so grab those ids.
                $gradable_array = array_intersect($unit->gradable_module_ids, $mandatory);
				
                // Now get the passes
                $passed = array_filter($unit->gradable_passed);
				
                // Remove non-mandatory graded items, irrelevant for this calculartion
                foreach ( array_keys($passed) as $pass_id ) {
                    if ( !in_array($pass_id, array_keys($gradable_array)) ) {
                        unset($passed[$pass_id]);
                    }
                }

                $required_in_answers = count($gradable_array);

                $in_answered = 0;
                foreach ( array_keys($passed) as $pass_id ) {
                    if ( in_array($pass_id, array_keys($answered)) ) {
                        $in_answered += 1;
                    }
                }

                $not_in_answers = !empty($gradable_array) ? $required_in_answers - $in_answered : 0;
                $answers_adjusted = count($answered) - $required_in_answers;
                $remaining = count($mandatory) - $answers_adjusted - $required_in_answers + $not_in_answers;

                $unit->remaining_mandatory_items = $remaining;
            }
        }

        function get_total_steps() {
            foreach ( $this->units as $unit ) {
                $total_steps = $unit->page_count;
                $total_steps += count($unit->mandatory_module_ids);

                $unit->total_steps = $total_steps;
            }
        }

        function get_completed_steps() {
            foreach ( $this->units as $unit ) {
                $completed_steps = count($unit->pages_visited);
                $completed_steps += count($unit->mandatory_module_ids) - $unit->remaining_mandatory_items;

                $unit->completed_steps = $completed_steps;				
            }
        }

        function get_completion() {
            foreach ( $this->units as $unit ) {
                $completion = $unit->completed_steps / $unit->total_steps * 100;

                $unit->completion = ( int ) $completion;

            }
        }

        function init_student_status( $student_id = 0 ) {
            $student_id = !empty($student_id) ? $student_id : get_current_user_id();

            $this->init_pages_visited($student_id);
            $this->check_pages_visited($student_id);

            $this->init_mandatory_modules_answered($student_id);
            $this->check_mandatory_modules_answered($student_id);

            $this->init_gradable_modules_passed($student_id);
            $this->check_gradable_modules_passed($student_id);

            $this->get_remaining_mandatory_items();
            $this->get_total_steps();
            $this->get_completed_steps();
            $this->get_completion();
			
        }

        function unit_progress( $unit_id ) {

            if ( !in_array($unit_id, array_keys($this->unit_index)) ) {
                return false;
            } else {

                // Get the correct unit
                $unit = $this->units[$this->unit_index[$unit_id]];

                return( $unit->completion );
            }
        }

        function unit_mandatory_steps( $unit_id ) {

            if ( !in_array($unit_id, array_keys($this->unit_index)) ) {
                return false;
            } else {

                // Get the correct unit
                $unit = $this->units[$this->unit_index[$unit_id]];

                return count($unit->mandatory_module_ids);
            }
        }

        function unit_completed_mandatory_steps( $unit_id ) {

            if ( !in_array($unit_id, array_keys($this->unit_index)) ) {
                return false;
            } else {

                // Get the correct unit
                $unit = $this->units[$this->unit_index[$unit_id]];

                return count($unit->mandatory_module_ids) - $unit->remaining_mandatory_items;
            }
        }

        function course_progress() {
            $total = 0;
            foreach ( $this->units as $unit ) {
                $total += $unit->completion;
            }
            if ( count($this->units) > 0 ) {
                $total = $total / count($this->units);
            } else {
                $total = 0;
            }
            return ( int ) $total;
        }

        function is_unit_complete( $unit_id ) {
            if ( !in_array($unit_id, array_keys($this->unit_index)) ) {
                return false;
            } else {

                // Get the correct unit
                $unit = $this->units[$this->unit_index[$unit_id]];

                return 100 == $unit->completion ? true : false;
            }
        }

        function is_course_complete() {
            $course_complete = !empty($this->units) ? true : false;
            foreach ( $this->units as $unit ) {
                $course_complete &= $this->is_unit_complete($unit->ID);
            }

            return $course_complete;
        }

    }

}