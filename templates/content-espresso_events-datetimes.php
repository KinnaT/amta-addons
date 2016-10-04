<?php
//echo '<br/><h6 style="color:#2EA2CC;">'. __FILE__ . ' &nbsp; <span style="font-weight:normal;color:#E76700"> Line #: ' . __LINE__ . '</span></h6>';

if ( is_single() || is_archive() && espresso_display_datetimes_in_event_list() ) :
global $post;
do_action( 'AHEE_event_details_before_event_date', $post );
?>
    <div class="event-datetimes">
        <?php
    // returns an unordered list of dates for an event
    function espresso_new_list_of_event_dates( $EVT_ID = 0, $date_format = '', $time_format = '', $echo = TRUE, $show_expired = NULL, $format = TRUE, $add_breaks = TRUE, $limit = NULL ) {
        $date_format = ! empty( $date_format ) ? $date_format : get_option( 'date_format' );
        $time_format = ! empty( $time_format ) ? $time_format : get_option( 'time_format' );
        $date_format = apply_filters( 'FHEE__espresso_list_of_event_dates__date_format', $date_format );
        $time_format = apply_filters( 'FHEE__espresso_list_of_event_dates__time_format', $time_format );
        $datetimes = EEH_Event_View::get_all_date_obj( $EVT_ID, $show_expired, FALSE, $limit );
        if ( ! $format ) {
            return apply_filters( 'FHEE__espresso_list_of_event_dates__datetimes', $datetimes );
        }
        //d( $datetimes );
        if ( is_array( $datetimes ) && ! empty( $datetimes )) {
            if ( count( $datetimes ) <= 2 ) {
                global $post;
                $html = $format ? '<ul id="ee-event-datetimes-ul-' . $post->ID . '" class="ee-event-datetimes-ul ee-clearfix">' : '';
                foreach ( $datetimes as $datetime ) {
                    if ( $datetime instanceof EE_Datetime ) {
                        $html .= '<li id="ee-event-datetimes-li-' . $datetime->ID();
                        $html .= '" class="ee-event-datetimes-li ee-event-datetimes-li-' . $datetime->get_active_status() . '">';
                        $datetime_name = $datetime->name();
                        $html .= ! empty( $datetime_name ) ? '<strong>' . $datetime_name . '</strong>' : '';
                        $html .= ! empty( $datetime_name )  && $add_breaks ? '<br />' : '';
                        $html .= '<span class="dashicons dashicons-calendar"></span>' . $datetime->date_range( $date_format ) . '<br/>';
                        $html .= '<span class="dashicons dashicons-clock"></span>' . $datetime->time_range( $time_format );
                        $datetime_description = $datetime->description();
                                $html .= ! empty( $datetime_description )  && $add_breaks ? '<br />' : '';
                        $html .= ! empty( $datetime_description ) ? ' - ' . $datetime_description : '';
                        $html = apply_filters( 'FHEE__espresso_list_of_event_dates__datetime_html', $html, $datetime );
                        $html .= '</li>';
                    }
                }
                $html .= $format ? '</ul>' : '';
            } else {
                global $post;
                echo "";
                $html = $format ? '<a class="btn btn-primary datetime-list toggle-button" href="#"><span class="dashicons dashicons-calendar-alt"></span>Show Event Sessions</a><div class="datetime-container"><ul id="ee-event-datetimes-ul-' . $post->ID . '" class="ee-event-datetimes-ul ee-clearfix">' : '';
                foreach ( $datetimes as $datetime ) {
                    if ( $datetime instanceof EE_Datetime ) {
                        $html .= '<li id="ee-event-datetimes-li-' . $datetime->ID();
                        $html .= '" class="ee-event-datetimes-li ee-event-datetimes-li-' . $datetime->get_active_status() . '">';
                        $datetime_name = $datetime->name();
                        $html .= ! empty( $datetime_name ) ? '<strong>' . $datetime_name . '</strong>' : '';
                        $html .= ! empty( $datetime_name )  && $add_breaks ? '<br />' : '';
                        $html .= '<span class="dashicons dashicons-calendar"></span>' . $datetime->date_range( $date_format ) . '<br/>';
                        $html .= '<span class="dashicons dashicons-clock"></span>' . $datetime->time_range( $time_format );
                        $datetime_description = $datetime->description();
                                $html .= ! empty( $datetime_description )  && $add_breaks ? '<br />' : '';
                        $html .= ! empty( $datetime_description ) ? ' - ' . $datetime_description : '';
                        $html = apply_filters( 'FHEE__espresso_list_of_event_dates__datetime_html', $html, $datetime );
                        $html .= '</li>';
                    }
                }
                $html .= $format ? "</ul></div>" : "";
            };
        } else {
            $html = $format ?  '<p><span class="dashicons dashicons-marker pink-text"></span>' . __( 'There are no upcoming dates for this event.', 'event_espresso' ) . '</p><br/>' : '';
        }
        if ( $echo ) {
            echo $html;
            return '';
        }
        return $html;
    }
        espresso_new_list_of_event_dates( $post->ID );
?>
    </div>
    <!-- .event-datetimes -->
<?php
do_action( 'AHEE_event_details_after_event_date', $post );
endif;
?>
