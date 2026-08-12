<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Pickup_Scheduler {

    /**
     * Determine if the store is open today (Europe/Berlin Timezone)
     */
    public static function is_store_open_today() {
        try {
            $timezone = new DateTimeZone('Europe/Berlin');
            $now = new DateTime('now', $timezone);
            $day_of_week = strtolower($now->format('l')); // Returns lowercase e.g., 'monday'
            
            $option_name = 'pickup_open_' . $day_of_week;
            $is_open = get_option( $option_name, 'yes' ); // Default to 'yes' (open)
            
            return ( 'yes' === $is_open );
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Determine if the store is currently open right now (considering overnight hours)
     */
    public static function is_store_currently_open() {
        try {
            $timezone = new DateTimeZone('Europe/Berlin');
            $now = new DateTime('now', $timezone);
            $current_time = $now->format('H:i');
            $day_of_week = strtolower($now->format('l'));
            
            $yesterday = clone $now;
            $yesterday->modify('-1 day');
            $yesterday_day = strtolower( $yesterday->format('l') );

            $day_hours = tsw_get_day_opening_hours( $day_of_week );
            $opening   = $day_hours['open'];
            $closing   = $day_hours['close'];

            $yesterday_hours   = tsw_get_day_opening_hours( $yesterday_day );
            $yesterday_opening = $yesterday_hours['open'];
            $yesterday_closing = $yesterday_hours['close'];

            $is_open_today     = ( get_option( 'pickup_open_' . $day_of_week, 'yes' ) === 'yes' );
            $is_open_yesterday = ( get_option( 'pickup_open_' . $yesterday_day, 'yes' ) === 'yes' );

            if ( $closing >= $opening ) {
                // Normal hours (e.g. 11:30 to 22:00)
                if ( $is_open_today && $current_time >= $opening && $current_time <= $closing ) {
                    return true;
                }
                // Check if yesterday was open with overnight hours extending into early morning today
                if ( $yesterday_closing < $yesterday_opening && $is_open_yesterday && $current_time <= $yesterday_closing ) {
                    return true;
                }
                return false;
            } else {
                // Overnight hours (e.g. 17:00 to 02:00 the next day)
                // We are open today if it's after opening
                if ( $is_open_today && $current_time >= $opening ) {
                    return true;
                }
                // We are open early morning if yesterday was open and it's before yesterday's closing
                if ( $is_open_yesterday && $current_time <= $yesterday_closing ) {
                    return true;
                }
                return false;
            }
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Determine if a specific time has already passed (Europe/Berlin Timezone)
     * Bug #2 fix: added optional $date param so future-date slots are not wrongly blocked
     */
    public static function is_pickup_time_passed( $time_str, $date_str = '' ) {
        if ( empty( $time_str ) ) {
            return false;
        }
        try {
            $timezone = new DateTimeZone('Europe/Berlin');
            $now = new DateTime('now', $timezone);
            $today_str = $now->format('Y-m-d');

            // If a future date is provided, the slot cannot be in the past
            if ( ! empty( $date_str ) && $date_str !== $today_str ) {
                return false;
            }

            $current_time_str = $now->format('H:i');

            $opening = get_option( 'pickup_opening_time', '11:30' );
            $closing = get_option( 'pickup_closing_time', '22:00' );

            // Base date is today
            $slot_date = clone $now;

            if ( $closing < $opening ) {
                // Overnight logic (e.g., 11:00 to 02:00)
                if ( $current_time_str >= $opening ) {
                    if ( $time_str < $opening ) {
                        $slot_date->modify('+1 day');
                    }
                } elseif ( $current_time_str <= $closing ) {
                    if ( $time_str >= $opening ) {
                        $slot_date->modify('-1 day');
                    }
                }
            }

            list($h, $m) = explode(':', $time_str);
            $slot_date->setTime((int)$h, (int)$m, 0);

            $buffer = intval( get_option( 'pickup_lead_time_buffer', '25' ) );
            $cutoff = clone $now;
            if ( $buffer > 0 ) {
                $cutoff->modify('+' . intval($buffer) . ' minutes');
            } else {
                $cutoff->modify('-5 minutes');
            }
            return $slot_date < $cutoff;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Find the nearest/first available future time slot
     */
    public static function get_first_available_pickup_time() {
        if ( ! self::is_store_open_today() ) {
            return '';
        }
        $choices = self::get_pickup_time_choices();
        foreach ( $choices as $value => $label ) {
            if ( $value !== '' && ! self::is_pickup_time_passed( $value ) ) {
                return $value; // Return the first future available slot
            }
        }
        return ''; // Fallback to default empty option if closed
    }

    /**
     * Generate time intervals dynamically based on interval option
     */
    public static function get_pickup_time_choices() {
        // If the store is closed today, return only a "closed" notice
        if ( ! self::is_store_open_today() ) {
            return array( '' => __( 'Closed today', '2-step-webshop' ) );
        }

        $day_hours = tsw_get_day_opening_hours();
        $opening   = $day_hours['open'];
        $closing   = $day_hours['close'];
        $interval  = intval( get_option( 'pickup_time_interval', '15' ) );

        // Fallbacks in case format is incorrect
        if ( ! preg_match( '/^\d{2}:\d{2}$/', $opening ) ) { $opening = '11:30'; }
        if ( ! preg_match( '/^\d{2}:\d{2}$/', $closing ) ) { $closing = '22:00'; }

        list($start_h, $start_m) = explode(':', $opening);
        list($end_h, $end_m) = explode(':', $closing);

        $start_time = mktime((int)$start_h, (int)$start_m, 0);
        $end_time   = mktime((int)$end_h, (int)$end_m, 0);

        $choices = array('' => __( 'Select Time...', '2-step-webshop' ));

        if ( $start_time <= $end_time ) {
            $current = $start_time;
            while ($current <= $end_time) {
                $time_str = date('H:i', $current);
                $choices[$time_str] = $time_str;
                $current = strtotime('+' . $interval . ' minutes', $current);
            }
        } else {
            // Overnight business hours (e.g. 11:00 to 02:00 the next day)
            // Leg 1: start_time to midnight
            $current = $start_time;
            $midnight = mktime(23, 59, 59);
            while ($current <= $midnight) {
                $time_str = date('H:i', $current);
                $choices[$time_str] = $time_str;
                $current = strtotime('+' . $interval . ' minutes', $current);
            }
            // Leg 2: midnight (00:00) to end_time
            $current = mktime(0, 0, 0);
            while ($current <= $end_time) {
                $time_str = date('H:i', $current);
                $choices[$time_str] = $time_str;
                $current = strtotime('+' . $interval . ' minutes', $current);
            }
        }

        return $choices;
    }

    /**
     * Generate available order dates (7 choices starting from Today or Tomorrow)
     * Bug #12 fix: skip days when store is configured as closed
     */
    public static function get_available_order_dates() {
        try {
            $timezone = new DateTimeZone('Europe/Berlin');
            $now = new DateTime('now', $timezone);
            $current_time = $now->format('H:i');

            $opening = get_option( 'pickup_opening_time', '11:30' );
            $closing = get_option( 'pickup_closing_time', '22:00' );

            $start_offset = 0;

            if ( $closing >= $opening ) {
                if ( $current_time > $closing ) {
                    $start_offset = 1;
                }
            }

            $dates = array();
            $checked = 0;
            $added   = 0;

            while ( $added < 7 && $checked < 14 ) {
                $d = clone $now;
                $offset = $start_offset + $checked;
                if ( $offset > 0 ) {
                    $d->modify('+' . $offset . ' day');
                }
                $checked++;

                // Bug #12: skip days the store is closed
                $day_key = strtolower( $d->format('l') );
                $is_open = get_option( 'pickup_open_' . $day_key, 'yes' ) === 'yes';
                if ( ! $is_open ) {
                    continue;
                }

                $date_key = $d->format('Y-m-d');

                if ( $added === 0 && $offset === 0 ) {
                    $label = __( 'Today', '2-step-webshop' );
                } elseif ( $added === 0 && $offset === 1 ) {
                    $label = __( 'Tomorrow', '2-step-webshop' );
                } elseif ( $added === 1 && $start_offset === 1 ) {
                    $label = __( 'Tomorrow', '2-step-webshop' );
                } else {
                    $english_day = $d->format('l');
                    $label = __( $english_day, '2-step-webshop' );
                }

                $label .= ' (' . $d->format('d.m.') . ')';
                $dates[ $date_key ] = $label;
                $added++;
            }

            return $dates;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Generate available order dates with structured day and date labels
     */
    public static function get_available_order_dates_structured() {
        try {
            $timezone = new DateTimeZone('Europe/Berlin');
            $now = new DateTime('now', $timezone);
            $current_time = $now->format('H:i');
            $opening = get_option( 'pickup_opening_time', '11:30' );
            $closing = get_option( 'pickup_closing_time', '22:00' );
            $start_offset = 0;
            if ( $closing >= $opening ) {
                if ( $current_time > $closing ) {
                    $start_offset = 1;
                }
            }
            $dates = array();
            $checked = 0;
            $added   = 0;
            while ( $added < 7 && $checked < 14 ) {
                $d = clone $now;
                $offset = $start_offset + $checked;
                if ( $offset > 0 ) {
                    $d->modify('+' . $offset . ' day');
                }
                $checked++;
                $day_key = strtolower( $d->format('l') );
                $is_open = get_option( 'pickup_open_' . $day_key, 'yes' ) === 'yes';
                if ( ! $is_open ) {
                    continue;
                }
                $date_key = $d->format('Y-m-d');
                if ( $added === 0 && $offset === 0 ) {
                    $label = __( 'Today', '2-step-webshop' );
                } elseif ( $added === 0 && $offset === 1 ) {
                    $label = __( 'Tomorrow', '2-step-webshop' );
                } elseif ( $added === 1 && $start_offset === 1 ) {
                    $label = __( 'Tomorrow', '2-step-webshop' );
                } else {
                    $english_day = $d->format('l');
                    $label = __( $english_day, '2-step-webshop' );
                }
                $dates[ $date_key ] = array(
                    'day_label'  => $label,
                    'date_label' => date_i18n( 'd F', $d->getTimestamp() )
                );
                $added++;
            }
            return $dates;
        } catch (Exception $e) {
            return array();
        }
    }
}
