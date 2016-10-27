	/**
	 * get total number of events today
	 *
	 * @access public
	 * @return int
	 */
	public function total_events_today() {
		$start = EEM_Datetime::instance()->convert_datetime_for_query(
			'DTT_EVT_start',
			date( 'Y-m-d' ) . ' 00:00:00',
			'Y-m-d H:i:s',
			'UTC'
		);
		$end = EEM_Datetime::instance()->convert_datetime_for_query(
			'DTT_EVT_start',
			date( 'Y-m-d' ) . ' 23:59:59',
			'Y-m-d H:i:s',
			'UTC'
		);
		$where = array(
			'Datetime.DTT_EVT_start' => array( 'BETWEEN', array( $start, $end ) ),
		);
		$count = EEM_Event::instance()->count( array( $where, 'caps' => 'read_admin' ), 'EVT_ID', true );
		return $count;
	}