<?php
class date{
	static function part( $part, $date = null ){
		$time = self::get( $date );
		$pos = strpos( $part, 'w' );
		if( $pos ){
			$part = str_replace( 'w', self::part_( 'w', $time ), $part );
		}
		$pos = strpos( $part, 'a' );
		if( $pos ){
			$part = str_replace( 'a', self::part_( 'a', $time ), $part );
		}
		return date( $part, $time );
	}
	static private function part_( $part, $date = null ){
		switch( $part ){
		case'a':
			switch( date( 'a', $date ) ){
			case'am': return '오전';
			case'pm': return '오후';
			}
			break;
		case'w':
			switch( date( 'w', $date ) ){
			case 0: return '일';
			case 1: return '월';
			case 2: return '화';
			case 3: return '수';
			case 4: return '목';
			case 5: return '금';
			case 6: return '토';
			}
			break;
		}
	}
	static function add( $interval, $number, $date = null, $part = 'Y-m-d H:i:s' ){
		$time = self::get( $date );
		switch( strtolower( $interval ) ){
		case'y': //year
			$time = strtotime( ($number).' year', $time ); break;
		case'm': //month
			$time = strtotime( self::part( 'Y-m',$time ) ).'-01';
			$time = strtotime( ($number).' month', $time ); break;
		case'd': //day
			$time = strtotime( ($number).' day', $time ); break;
		case'h': //hour
			$time = strtotime( ($number).' hour', $time ); break;
		case'i': //minute
			$time = strtotime( ($number).' minute', $time ); break;
		case's': //second
			$time = strtotime( ($number).' second', $time ); break;
		default: 
			return null;
		}
		return self::part( $part, $time );
	}
	static function diff( $interval, $dateOld, $dataNew = NULL ){
		$date1 = self::get( $dateOld );
		$date2 = self::get( $dataNew );
		switch( strtolower( $interval ) ){
		case'y': //year
			return self::part( 'y', $date2 ) - self::part( 'y', $date1 );
		case'm': //month
			return ( self::part( 'y', $date2 ) - self::part( 'y', $date1 ) ) * 12 + self::part( 'm', $date2 ) - self::part( 'm', $date1 );
		case'd': //day
			if( $date2 > $date1 ){
				$order = 1;
			}else{
				$order = -1;
				$date1 = self::get( $dataNew );
				$date2 = self::get( $dateOld );
			}
			$d1_year = self::part( 'Y', $date1 );
			$d1_month = self::part( 'n', $date1 );
			$d1_date = self::part( 'j', $date1 );
			$d2_year = self::part( 'Y', $date2 );
			$d2_month = self::part( 'n', $date2 );
			$d2_date = self::part( 'j', $date2 );
			
			$j = $d2_year - $d1_year;
			$d = 0;
			if( $j > 0 ){
				$d += self::diff( 'd', self::mktime( $d1_year, $d1_month, $d1_date ), self::mktime( $d1_year, 12, 31 ) );
				$d += self::diff( 'd', self::mktime( $d2_year, 1, 1 ), self::mktime( $d2_year, $d2_month, $d2_date ) );
				$year = $d1_year + 2;
				for( $i = 2 ; $i < $j - 1 ; $i++ ){
					if( self::leapYear( $year ) ){
						$d += 366;
					}else{
						$d += 365;
					}
					$year++;
				}
			}else{
				$temp = array( null, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
				if( self::leapYear( $d1_year ) ) $temp[2]++;
				$j = $d2_month - $d1_month;
				if( $j > 0 ){
					$d += self::diff( 'd', self::mktime( $d1_year, $d1_month, $d1_date ), self::mktime( $d1_year, $d1_month, $temp[$d1_month] ) ) + 1;
					$d += self::diff( 'd', self::mktime( $d2_year, $d2_month, 1 ), self::mktime( $d2_year, $d2_month, $d2_date ) );
					$month = $d1_month + 1;
					for( $i = 1 ; $i < $j ; $i++ ){
						$d += $temp[$month++];
					}
				}else{
					$d += $d2_date - $d1_date;
				}
			}
			return $d * $order;
			break;
		case'h': //hour
			return (int)( ( $date2 - $date1 ) / 3600 );
		case'i': //minute
			return (int)( ( $date2 - $date1 ) / 60 );
		case's': //second
			return $date2 - $date1;
		default: 
			return NULL;
		}
	}
	static private function leapYear( $year ){
		return ( $year % 4 == 0 && $year % 100 != 0 ) || $year % 400 == 0;
	}
	static function mktime( $y, $m, $d, $h = 0, $i = 0, $s = 0 ){
		//echo( $y.','.$m.','.$d.','.$h.','.$i.','.$s.'<br>' );
		return mktime( $h, $i, $s, $m, $d, $y );
	}
	static private function get( $date = NULL ){
		if( gettype( $date ) == 'integer' ){
			return $date;
		}else if( $date ){
			if( strpos( $date, '-' ) === false ){
				return (int)$date;
			}else{
				$i = explode( '-', $date );
				$h = $m = $s = 0;
				if( strpos( $i[2], ' ' ) ){
					$temp = explode( ' ', $i[2] );
					$i[2] = $temp[0];
					$temp = explode( ':', $temp[1] );
					$h = (int)$temp[0];
					$m = (int)$temp[1];
					$s = (int)$temp[2];
				}
				return self::mktime( (int)$i[0], (int)$i[1], (int)$i[2], $h, $m, $s );
			}
		}else{
			return time();
		}
	}
}
?>