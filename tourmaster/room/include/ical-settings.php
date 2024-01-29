<?php

    add_action('init', 'tourmaster_generate_ical_content');
    if( !function_exists('tourmaster_generate_ical_content') ){
        function tourmaster_generate_ical_content(){
            
            if( isset($_GET['tourmaster_room_ical']) && !empty($_GET['room_id']) && is_numeric($_GET['room_id']) ){

                global $wpdb;

                $content  = "BEGIN:VCALENDAR\n";
                $content .= "VERSION:2.0\n";

                $ical_start_time = tourmaster_get_option('room_general', 'ical-start-time', 2);
                $ical_start_time = date('Y-m-d 00:00:00', strtotime("-{$ical_start_time} month"));

                $sql  = "SELECT * FROM {$wpdb->prefix}tourmaster_room_booking ";
                $sql .= $wpdb->prepare('WHERE room_id = %d ', $_GET['room_id']);
                $sql .= $wpdb->prepare('AND start_date > %s', $ical_start_time);
                $results = $wpdb->get_results($sql);

                if( !empty($results) ){
                    foreach( $results as $result ){
                        $start_time = str_replace('-', '', $result->start_date);
                        $start_time = str_replace(' 00:00:00', '', $start_time);
                        $end_time = str_replace('-', '', $result->end_date);
                        $end_time = str_replace(' 00:00:00', '', $end_time);

                        $content .= "BEGIN:VEVENT\n";
                        $content .= "UID:" . trim($_GET['room_id']) . $start_time . "\n";
                        $content .= "DTSTAMP:" . $start_time . "T000000Z\n";
                        $content .= "DTSTART;VALUE=DATE:" . $start_time . "\n";
                        $content .= "DTEND;VALUE=DATE:" . $end_time . "\n";
                        $content .= "SUMMARY:" . get_the_title($_GET['room_id']) . "\n";
                        $content .= "END:VEVENT\n";
                    }
                }

                $content .= "END:VCALENDAR";

                header("Content-type:text/calendar");
                header('Content-Disposition: attachment; filename="tourmaster_ical.ics"');
                header('Content-Length: '.strlen($content));
                header('Connection: close');
                echo $content;
                exit();
            }
        }
    }

    if( !class_exists('tourmaster_room_ics') ){
		class tourmaster_room_ics{
		    
		    /* Function is to get all the contents from ics and explode all the datas according to the events and its sections */
		    function getIcsEventsAsArray($file) {
		        $icsDates = array();
		        $retDates = array();

		    	$icalString = tourmaster_get_async_icals($file);
		    	if( !empty($icalString) ){
		    		$icsData = explode("BEGIN:", $icalString);
		    	}

		        /* Iterating the icsData value to make all the start end dates as sub array */
		        if( !empty($icsData) ){
			        foreach( $icsData as $key => $value){
			            $icsDatesMeta[$key] = explode ( "\n", $value );
			        }
			    }

		        /* Itearting the Ics Meta Value */
		        if( !empty($icsDatesMeta) ){
			        foreach( $icsDatesMeta as $key => $value ) {
			            foreach ( $value as $subKey => $subValue ){
			                $icsDates = $this->getICSDates($key, $subKey, $subValue, $icsDates);
			            }

			            if( !empty($icsDates[$key]['DTSTART']) && !empty($icsDates[$key]['DTEND']) ){
			            	$retDates[] = array(
			            		'check-in' => $icsDates[$key]['DTSTART'],
			            		'check-out' => $icsDates[$key]['DTEND']
			            	);
			            }
			        }
		        }

		        return $retDates;
		    }

		    /* funcion is to avaid the elements wich is not having the proper start, end  and summary informations */
		    function getICSDates($key, $subKey, $subValue, $icsDates) {
		      
		       if( $key != 0 && $subKey == 0 ){
		            $icsDates[$key]["BEGIN"] = $subValue;
		       }else{
		            $subValueArr = explode(":", $subValue, 2);
		            if( isset($subValueArr[1]) ){
		            	if( strpos($subValueArr[0], 'DTSTART') !== false ){
		            		$subValueArr[0] = 'DTSTART';
		            	}else if( strpos($subValueArr[0], 'DTEND') !== false ){
		            		$subValueArr[0] = 'DTEND';
		            	}

		            	if( $subValueArr[0] == 'DTSTART' || $subValueArr[0] == 'DTEND' ){
		            		$subValueArr[1] = date('Y-m-d', strtotime($subValueArr[1]));
		            	}

		                $icsDates[$key][$subValueArr[0]] = $subValueArr[1];
		            }
		        }

		        return $icsDates;
		    }
		}
	}

    if( !function_exists('tourmaster_get_async_icals') ){
		function tourmaster_get_async_icals($file){
			global $tourmaster_room_async_icals;

			if( !empty($tourmaster_room_async_icals[$file]) ){
				return $tourmaster_room_async_icals[$file];
			}

			return array();
		}
	}

    if( !function_exists('tourmaster_set_async_icals') ){
		function tourmaster_set_async_icals($files){
			global $tourmaster_room_async_icals;
			$tourmaster_room_async_icals = array();

			$data = array();
			foreach( $files as $file ){
				$data[] = array(
					'url' => $file,
					'type' => 'GET'
				);
			}

			try{
				$responses = Requests::request_multiple($data);
			}catch(Exception $e){
				
			}
			
			if( !empty($responses) ){
				foreach( $responses as $response ){
					if( !empty($response->url) && !empty($response->body) ){
						if( !empty($response->history[0]->url) ){
							$tourmaster_room_async_icals[$response->history[0]->url] = $response->body;
						}else{
							$tourmaster_room_async_icals[$response->url] = $response->body;
						}
					}
				}
			}
		}
	}

    if( !function_exists('tourmaster_set_ical') ){
		function tourmaster_set_ical( $post_id, $file_url ){

            $ical_data = array();
			$old_data = get_post_meta($post_id, 'tourmaster_ical_sync_data', true);
			
			$ics = new tourmaster_room_ics();

			// check if there're multiple files
			if( strpos($file_url, "\r\n") !== false ){
				$files = explode("\r\n", $file_url);
			}else{
				$files = explode("\n", $file_url);
			}
			foreach( $files as $file ){
				if( !empty($file) ){
					$ical_data = array_merge($ical_data, $ics->getIcsEventsAsArray($file));
				}
			}

			// if the data is new, save it
			if( !empty($ical_data) && $old_data != $ical_data ){
				update_post_meta($post_id, 'tourmaster_ical_sync_data', $ical_data);

				// list date
				$ical_date_list = array();
				foreach( $ical_data as $ical_date ){
					$ical_date_list = array_merge($ical_date_list, tourmaster_split_date($ical_date['check-in'], $ical_date['check-out']));
				}
				$ical_date_list = array_unique($ical_date_list);
				update_post_meta($post_id, 'tourmaster_ical_sync_date_list', implode(',', $ical_date_list));

				tourmaster_room_calculate_date_display($result->post_id, array(
					'date-ical' => $ical_date_list
				));

				return true;
			}

			return false;
		}

	}

    add_action('init', 'tourmaster_ical_routine');
	if( !function_exists('tourmaster_ical_routine') ){
		function tourmaster_ical_routine(){
			global $wpdb;
			
			$current_time = strtotime('now');
			$timestamp = get_option('tourmaster_ical_sync_timestamp', 0);
            $cache_time = intval(tourmaster_get_option('room_general', 'ical-cache-time', 5)) * 60;
			
			if( empty($timestamp) || $timestamp + $cache_time < $current_time ){

				$sql  = "SELECT post_id, meta_value FROM {$wpdb->postmeta} ";
				$sql .= "WHERE meta_key = 'tourmaster_ical_sync_url' AND ";
				$sql .= "meta_value IS NOT NULL AND meta_value <> '' ";

				$results = $wpdb->get_results($sql);

				if( !empty($results) ){

					$files = array();
					foreach( $results as $result ){
						if( strpos($result->meta_value, "\r\n") !== false ){
							$files = array_merge($files, explode("\r\n", trim($result->meta_value)));
						}else{
							$files = array_merge($files, explode("\n", trim($result->meta_value)));
						}
					}
					tourmaster_set_async_icals($files);

					foreach( $results as $result ){
						tourmaster_set_ical($result->post_id, $result->meta_value);
					} 
				} 

				update_option('tourmaster_ical_sync_timestamp', $current_time);
			}
		} // gdlr_hotel_ical_routine
	}