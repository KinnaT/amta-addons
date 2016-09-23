
	/**
	 * This is the callback method for the duplicate event route
	 * Method looks for 'EVT_ID' in the request and retrieves that event and its details and duplicates them
	 * into a new event.  We add a hook so that any plugins that add extra event details can hook into this
	 * action.  Note that the dupe will have **DUPLICATE** as its title and slug.
	 * After duplication the redirect is to the new event edit page.
	 *
	 * @return void
	 * @access protected
	 * @throws EE_Error If EE_Event is not available with given ID
	 */
	 	protected function _duplicate_event() {
		// first make sure the ID for the event is in the request.
		//  If it isn't then we need to bail and redirect back to overview list table (cause how did we get here?)
		if ( ! isset( $this->_req_data['EVT_ID'] ) ) {
			EE_Error::add_error(
				esc_html__(
					'In order to duplicate an event an Event ID is required.  None was given.',
					'event_espresso'
				),
				__FILE__,
				__FUNCTION__,
				__LINE__
			);
			$this->_redirect_after_action( false, '', '', array(), true );
			return;
		}
		//k we've got EVT_ID so let's use that to get the event we'll duplicate
		$orig_event = EEM_Event::instance()->get_one_by_ID( $this->_req_data['EVT_ID'] );
		if ( ! $orig_event instanceof EE_Event ) {
			throw new EE_Error(
				sprintf(
					esc_html__( 'An EE_Event object could not be retrieved for the given ID (%s)', 'event_espresso' ),
					$this->_req_data['EVT_ID']
				)
			);
		}
		//k now let's clone the $orig_event before getting relations
		$new_event = clone $orig_event;
		//original datetimes
		$orig_datetimes = $orig_event->get_many_related( 'Datetime' );
		//other original relations
		$orig_ven = $orig_event->get_many_related( 'Venue' );
		//reset the ID and modify other details to make it clear this is a dupe
		$new_event->set( 'EVT_ID', 0 );
		$new_name = $new_event->name() . ' ' . esc_html__( '**DUPLICATE**', 'event_espresso' );
		$new_event->set( 'EVT_name', $new_name );
		$new_event->set(
			'EVT_slug',
			wp_unique_post_slug(
				sanitize_title( $orig_event->name() ),
				0,
				'publish',
				'espresso_events',
				0
			)
		);
		$new_event->set( 'status', 'draft' );
		//duplicate discussion settings
		$new_event->set( 'comment_status', $orig_event->get( 'comment_status' ) );
		$new_event->set( 'ping_status', $orig_event->get( 'ping_status' ) );
		//save the new event
		$new_event->save();
		//venues
		foreach ( $orig_ven as $ven ) {
			$new_event->_add_relation_to( $ven, 'Venue' );
		}
		$new_event->save();
		//now we need to get the question group relations and handle that
		//first primary question groups
		$orig_primary_qgs = $orig_event->get_many_related(
			'Question_Group',
			array( array( 'Event_Question_Group.EQG_primary' => 1 ) )
		);
		if ( ! empty( $orig_primary_qgs ) ) {
			foreach ( $orig_primary_qgs as $id => $obj ) {
				if ( $obj instanceof EE_Question_Group ) {
					$new_event->_add_relation_to( $obj, 'Question_Group', array( 'EQG_primary' => 1 ) );
				}
			}
		}
		//next additional attendee question groups
		$orig_additional_qgs = $orig_event->get_many_related(
			'Question_Group',
			array( array( 'Event_Question_Group.EQG_primary' => 0 ) )
		);
		if ( ! empty( $orig_additional_qgs ) ) {
			foreach ( $orig_additional_qgs as $id => $obj ) {
				if ( $obj instanceof EE_Question_Group ) {
					$new_event->_add_relation_to( $obj, 'Question_Group', array( 'EQG_primary' => 0 ) );
				}
			}
		}
		//now save
		$new_event->save();
		//k now that we have the new event saved we can loop through the datetimes and start adding relations.
		$cloned_tickets = array();
		foreach ( $orig_datetimes as $orig_dtt ) {
			if ( ! $orig_dtt instanceof EE_Datetime ) {
				continue;
			}
			$new_dtt = clone $orig_dtt;
			$orig_tkts = $orig_dtt->tickets();
			//save new dtt then add to event
			$new_dtt->set( 'DTT_ID', 0 );
			$new_dtt->set( 'DTT_sold', 0 );
			$new_dtt->save();
			$new_event->_add_relation_to( $new_dtt, 'Datetime' );
			$new_event->save();
			//now let's get the ticket relations setup.
			foreach ( (array) $orig_tkts as $orig_tkt ) {
				//it's possible a datetime will have no tickets so let's verify we HAVE a ticket first.
				if ( ! $orig_tkt instanceof EE_Ticket ) {
					continue;
				}
				//is this ticket archived?  If it is then let's skip
				if ( $orig_tkt->get( 'TKT_deleted' ) ) {
					continue;
				}
				// does this original ticket already exist in the clone_tickets cache?
				//  If so we'll just use the new ticket from it.
				if ( isset( $cloned_tickets[ $orig_tkt->ID() ] ) ) {
					$new_tkt = $cloned_tickets[ $orig_tkt->ID() ];
				} else {
					$new_tkt = clone $orig_tkt;
					//get relations on the $orig_tkt that we need to setup.
					$orig_prices = $orig_tkt->prices();
					$new_tkt->set( 'TKT_ID', 0 );
					$new_tkt->set( 'TKT_sold', 0 );
					$new_tkt->save(); //make sure new ticket has ID.
					//price relations on new ticket need to be setup.
					foreach ( $orig_prices as $orig_price ) {
						$new_price = clone $orig_price;
						$new_price->set( 'PRC_ID', 0 );
						$new_price->save();
						$new_tkt->_add_relation_to( $new_price, 'Price' );
						$new_tkt->save();
					}
				}
				// k now we can add the new ticket as a relation to the new datetime
				// and make sure its added to our cached $cloned_tickets array
				// for use with later datetimes that have the same ticket.
				$new_dtt->_add_relation_to( $new_tkt, 'Ticket' );
				$new_dtt->save();
				$cloned_tickets[ $orig_tkt->ID() ] = $new_tkt;
			}
		}
		//clone taxonomy information
		$taxonomies_to_clone_with = apply_filters(
			'FHEE__Extend_Events_Admin_Page___duplicate_event__taxonomies_to_clone',
			array( 'espresso_event_categories', 'espresso_event_type', 'post_tag' )
		);
		//get terms for original event (notice)
		$orig_terms = wp_get_object_terms( $orig_event->ID(), $taxonomies_to_clone_with );
		//loop through terms and add them to new event.
		foreach ( $orig_terms as $term ) {
			wp_set_object_terms( $new_event->ID(), $term->term_id, $term->taxonomy, true );
		}
		do_action( 'AHEE__Extend_Events_Admin_Page___duplicate_event__after', $new_event, $orig_event );
		//now let's redirect to the edit page for this duplicated event if we have a new event id.
		if ( $new_event->ID() ) {
			$redirect_args = array(
				'post'   => $new_event->ID(),
				'action' => 'edit',
			);
			EE_Error::add_success(
				esc_html__(
					'Event successfully duplicated.  Please review the details below and make any necessary edits',
					'event_espresso'
				)
			);
		} else {
			$redirect_args = array(
				'action' => 'default',
			);
			EE_Error::add_error(
				esc_html__( 'Not able to duplicate event.  Something went wrong.', 'event_espresso' ),
				__FILE__,
				__FUNCTION__,
				__LINE__
			);
		}
		$this->_redirect_after_action( false, '', '', $redirect_args, true );
	}



	/**
	 * wp_list_table_mods for caf
	 * ============================
	 */
	/**
	 * hook into list table filters and provide filters for caffeinated list table
	 *
	 * @param  array $old_filters    any existing filters present
	 * @param  array $list_table_obj the list table object
	 * @return array                  new filters
	 */
	public function list_table_filters( $old_filters, $list_table_obj ) {
		$filters = array();
		//first month/year filters
		$filters[] = $this->espresso_event_months_dropdown();
		$status = isset( $this->_req_data['status'] ) ? $this->_req_data['status'] : null;
		//active status dropdown
		if ( $status !== 'draft' ) {
			$filters[] = $this->active_status_dropdown(
				isset( $this->_req_data['active_status'] ) ? $this->_req_data['active_status'] : ''
			);
		}
		//category filter
		$filters[] = $this->category_dropdown();
		return array_merge( $old_filters, $filters );
	}
