class cronTranslator 
{


	// Cron values translate to datetime format YmdHis
	public static function cronValuesToNexDateTime( $values )
	{

		$ctMinute = $values['ctMinute']; // (from 0 to 59)
		$ctHour = $values['ctHour']; // (from 0 to 23)
		$ctDayOfMonth = $values['ctDayOfMonth']; // (from 1 to 31)
		$ctMonth = $values['ctMonth']; // (from 1 to 12)
		$ctDayOfWeek = $values['ctDayOfWeek']; // (from 0 to 6) (0=Sunday)

		// Run every minute.
		if( $ctMinute === '*' && $ctHour === '*' && $ctDayOfMonth === '*' && $ctMonth === '*' && $ctDayOfWeek === '*' ) {
			return ''.date('YmdHi').'00';
		};

		$minute = self::parseMinute( $ctMinute ); // created. Not checked.
		$hour = self::parseHour( $ctHour ); // created. Not checked.
		// self::parseDayOfMonth( $value );
		// self::parseMonth( $value );
		// self::parseDayOfWeek( $value );


		$hourPlus = ($minute['incr'] == 1 )
			? 1
			: 0;

		$dayPlus = ($hour['incr'] == 1 )
			? 1
			: 0;

// echo '<prv>';print_r( $hour );exit;
		return ''.date('YmdHis', mktime(
				$hour['hour'] + $hourPlus,
				$minute['min'],
				0,
				date('m'),
				date('d') + $dayPlus,
				date('Y')
			));
	}




	// Parse cron minute value to next run minute.
	// Return array: 1 - str with 2 digits: 00, 05, 23, .. 59  2 - if "1", increment hour by 1.
	// Posible formats:
	// * - run every minute
	// 0 - run in given minute.
	// 1,5,30,52 - run in every set minute.
	// 1-15 - run every minute in given period.
	// */10 - run every 10 minutes.
	private static function parseMinute( $value )
	{
		// Current minute;
		$minute = (int)date('i');

		if($value === '*') {
			$minute++;
			return array('min' => $minute, 'incr' => 0);
		};


		if( $value === '0' || $value === '00' )
			return array('min' => 0, 'incr' => 0);



		// If simple number in value.
		if( !strpbrk($value, ',/- ') ) 
		{
			$value = (int)$value;

			// If not minutes number.
			if( !($value >= 0 || $value <= 59))
				return array('min' => 0, 'incr' => 0);

			$incr = ( $value >= $minute ) ? 0 : 1;
			return array('min' => $vaule, 'incr' => $incr);
		};


		$arr = explode(',', $value);

		// Variable for foreach fill.
		$arrTarget = array('min' => 0, 'incr' => 1 ); 

		// Find next minute.
		foreach ($arr as $val) 
		{
			$val = str_replace( ' ', '', $val );

			// Check for "/". "*/10". Run every /under minutes.
			$position = strpos($val, '/');

			if( !($position === false) )
			{
				$num = (int)substr($val, $position+1);

				$target = 0;
				$count = 1;
				$stop = 0;
				while ( $target <= 59 )
				{
					$target = $num * $count;
					if( $target === $minute ) {
						// $target = ( $target < 10 ) ? '0'.$target : $target;
						$arrTarget['min'] = $target;
						$arrTarget['incr'] = 0;
						$stop = 1;
						break;
					};

					if( $target > $minute && $target <= 60 )
					{
						if( $target === 60 ) {
							$arrTarget['min'] = 0;
							$arrTarget['incr'] = 1;
							$stop = 1;
							break;
						};

						$arrTarget['min'] = $target;
						$arrTarget['incr'] = 0;
						$stop = 1;
						break;					
					};

					$count++;
				};

				if( $stop == 1 ) {
					break;
				};

				// Forward to nex hour.
				// $num = ( $num < 10 ) ? '0'.$num : $num;
				$arrTarget['min'] = $num;
				$arrTarget['incr'] = 1;
				break;
			};

			// Check for "-". "5-45". Run in interval.
			$position = strpos($val, '-');

			if( !($position === false) )
			{
				$n0 = (int)substr($val, 0, $position);
				$n1 = (int)substr($val, $position+1);
				if( $n0 < $minute && $minute < $n1 ) {
					$minute++;
					$arrTarget['min'] = $minute;
					$arrTarget['incr'] = 0;
					break;
				};
			};


			// Simple number.
			$val = (int)$val;
			if( $val < $minute ){
				$arrTarget['min'] = $val;
				$arrTarget['incr'] = 1;
				continue;
			};

			if( $val >= $minute && $val <= 59 )
			{
				$arrTarget['min'] = $val;
				$arrTarget['incr'] = 0;
				break;
			};
		};

		return $arrTarget;
	}



	// Parse cron hour value to next run minute.
	// Return str with 2 digits: 00, 05, 20, .. 23
	// Posible formats:
	// * - run every hour
	// 0 - run in given hour.
	// 1,5,16,23 - run in every set hour.
	// 1-15 - run every hour in given period.
	// */2 - run every 2 hour.
	private static function parseHour( $value )
	{
		// Current hour;
		$hour = (int)date('H');


		if($value === '*') 
		{
			$min = (int)date('i');

			if($min === 59 )
				$hour++;
			
			return array('hour' => $hour, 'incr' => 0);
		};


		if( $value === '0' || $value === '00' )
			return array('hour' => 0, 'incr' => 0);



		// If simple number in value.
		if( !strpbrk($value, ',/- ') ) 
		{
			$value = (int)$value;

			// If not hours number.
			if( !($value >= 0 || $value <= 23))
				return array('hour' => 0, 'incr' => 0);

			$incr = ( $value >= $hour ) ? 0 : 1;

			// $vaule = ($value < 10) ? '0'.$value : $value;
			return array('hour' => $vaule, 'incr' => $incr);
		};


		$arr = explode(',', $value);

		// Variable for foreach fill.
		$arrTarget = array('hour' => 0, 'incr' => 1 ); 

		// Find next hour.
		foreach ($arr as $val) 
		{
			$val = str_replace( ' ', '', $val );

			// Check for "/". "*/10". Run every /under hours.
			$position = strpos($val, '/');

			if( !($position === false) )
			{
				$num = (int)substr($val, $position+1);

				$target = 0;
				$count = 1;
				$stop = 0;
				while ( $target <= 23 )
				{
					$target = $num * $count;
					if( $target === $hour ) {
						// $target = ( $target < 10 ) ? '0'.$target : $target;
						$arrTarget['hour'] = $target;
						$arrTarget['incr'] = 0;
						$stop = 1;
						break;
					};

					if( $target > $hour && $target <= 24 )
					{
						if( $target === 24 ) {
							$arrTarget['hour'] = 0;
							$arrTarget['incr'] = 1;
							$stop = 1;
							break;
						};

						$arrTarget['hour'] = $target;
						$arrTarget['incr'] = 0;
						$stop = 1;
						break;					
					};

					$count++;
				};

				if( $stop == 1 ) {
					break;
				};

				// Forward to nex hour.
				// $num = ( $num < 10 ) ? '0'.$num : $num;
				$arrTarget['hour'] = $num;
				$arrTarget['incr'] = 1;
				break;
			};

			// Check for "-". "5-45". Run in interval.
			$position = strpos($val, '-');

			if( !($position === false) )
			{
				$n0 = (int)substr($val, 0, $position);
				$n1 = (int)substr($val, $position+1);
				if( $n0 < $hour && $hour < $n1 ) {
					$hour++;
					// $target = ($hour < 10) ? '0'.$hour : $hour;
					$arrTarget['hour'] = $hour;
					$arrTarget['incr'] = 0;
					break;
				};
			};


			// Simple number.
			$val = (int)$val;
			if( $val < $hour ){
				$arrTarget['hour'] = $val;
				$arrTarget['incr'] = 1;
				continue;
			};

			if( $val >= $hour && $val <= 23 )
			{
				$arrTarget['hour'] = $val;
				$arrTarget['incr'] = 0;
				break;
			};
		};

		return $arrTarget;
	}


}
