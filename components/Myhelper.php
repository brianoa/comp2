<?php

namespace app\components;

use app\models\PermissionGroup;
use app\models\Users;
use app\models\Outbox;
use app\models\Template;
use app\components\OutboxJob;
use Webpatser\Uuid\Uuid;
use Yii;
use yii\base\Component;
use yii\helpers\Html;
use yii\models;
use DateTime;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class Myhelper
 * @package app\components
 */
class Myhelper extends Component {
	/**
	 * Method to check if a user is authorised to
	 *
	 * @param string $username
	 *
	 * @return string
	 */
	public static function isAuthorised( $username, $perm = [] ) {
		$return   = false;
		$loaduser = Users::find()->where( "email = '{$username}'" )->one();
		if ( $loaduser ) {
			$usergrp             = $loaduser->perm_group;
			$usrgrpobj           = PermissionGroup::findOne( $usergrp );
			$groupperm           = explode( ',', $usrgrpobj->defaultPermissions );
			$extuserperm         = $loaduser->extpermission; // To continue
			$extpermarray        = explode( ',', $extuserperm );
			$deniedpermarray     = explode( ',', $loaduser->defaultpermissiondenied );
			$thisuserperms       = array_merge( $extpermarray, $groupperm );
			$thisUserAllowedPerm = array_diff( $thisuserperms, $deniedpermarray );
			$isauth              = array_intersect( $perm, $thisUserAllowedPerm );
			if ( $isauth ) {
				$return = true;
			}
		}

		return $return;
	}

	

	/**
	 * Encode array from latin1 to utf8 recursively
	 *
	 * @param $dat
	 *
	 * @return array|string
	 */
	public static function convert_from_latin1_to_utf8_recursively( $dat ) {
		if ( is_string( $dat ) ) {
			return utf8_encode( $dat );
		} elseif ( is_array( $dat ) ) {
			$ret = [];
			foreach ( $dat as $i => $d ) {
				$ret[ $i ] = self::convert_from_latin1_to_utf8_recursively( $d );
			}

			return $ret;
		} elseif ( is_object( $dat ) ) {
			foreach ( $dat as $i => $d ) {
				$dat->$i = self::convert_from_latin1_to_utf8_recursively( $d );
			}

			return $dat;
		} else {
			return $dat;
		}
	}

	
	public static function generateRandomString( $length = 10 ) {
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyz@!$#&%$+=)({}@ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}

		return $randomString;
	}

	public static function checkRemoteAddress() {
		return;
		if (in_array($_SERVER['SERVER_NAME'],array('comp21.com')) ) {
			//allow execution for localhost
		}
		else if (in_array($_SERVER['REMOTE_ADDR'],array('18.222.117.89','172.31.14.172')) ) {
			//allow execution for coke
		}
		else if (in_array($_SERVER['REMOTE_ADDR'],array('18.190.157.46','172.31.41.69')) ) {
			//allow execution for net
		}
		else if (in_array($_SERVER['REMOTE_ADDR'],array('3.15.207.63','172.31.46.231')) ) {
			//allow execution for dev
		}
		else if (in_array($_SERVER['REMOTE_ADDR'],array('18.188.246.230','172.31.46.248')) ) {
			//allow execution for cmedia
		}
		else if (in_array($_SERVER['REMOTE_ADDR'],array('3.134.91.9','172.31.36.164')) ) {
			//allow execution for efmtz
		}
		else {
			
		}
	}

	/**
	 * @return array
	 */
	public static function ListMonth() {
		$arr = [];
		for ( $i = 1; $i <= 12; $i ++ ) {
			$arr[ $i ] = $i;
		}

		return $arr;
	}

	public static function listMonthByName(){
		$months=[];
		$timeline = 12;
		for ( $i = 1; $i <= $timeline; $i ++ ) {
			$timestamp = mktime( 0, 0, 0,  $i, 1 );
			$months[$i] = date( 'F', $timestamp );
		}
		return $months;
	}

	/**
	 * @return array
	 */
	public static function ListYear() {
		// use this to set an option as selected (ie you are pulling existing values out of the database)
		$arr           = [];
		$starting_year = 1998;
		$ending_year   = 2030;
		$years         = [];
		for ( $starting_year; $starting_year <= $ending_year; $starting_year ++ ) {
			$years[] = $starting_year;
		}

		return $years;
	}

	/**
	 * a function to generate a 24 hours time clock
	 * @return array
	 */
	public static function generate24hoursTimeClock() {
		$arr = [];
		for ( $i = 0; $i < 25; $i ++ ) {
			$timee = $i . ":00:00";
			array_push( $arr, $timee );
		}

		return $arr;
	}

	
	/**
	 *
	 * @param integer $prevbyTime number of days/hours/second that have elapsed
	 * @param string $unitOfmeasurement days/hours/second etc
	 *
	 * @param $prevbyTime
	 * @param $unitOfmeasurement
	 * @param string $
	 *
	 * @return false|string
	 */
	public function getPrevDatetime( $prevbyTime, $unitOfmeasurement, $date = '' ) {
		if ( $date != '' ) {
			$today = $date;
		} else {
			$today = date( 'Y-m-d H:i:s' );
		}
		$todaytimestamp   = strtotime( $today );
		$elapsedtimestamp = strtotime( "-{$prevbyTime} $unitOfmeasurement", $todaytimestamp );
		$elapseddate      = date( 'Y-m-d H:i:s', $elapsedtimestamp );

		return $elapseddate;
	}

	/**
	 * Method to get yesterday date- helper
	 */
	public function getYesterdayDate() {
		$date = date( 'Y-m-d' );
		if ( date( 'w', strtotime( $date ) ) == 0 ) { //if Sunday go back to friday
			$yesterday = strtotime( Yii::$app->myhelper->getPrevDatetime( 2, 'days', $date ) );
		} else if ( date( 'w', strtotime( $date ) ) == 1 ) {//if Monday go back to friday
			$yesterday = strtotime( Yii::$app->myhelper->getPrevDatetime( 3, 'days', $date ) );
		} else {
			$yesterday = strtotime( Yii::$app->myhelper->getPrevDatetime( 1, 'days', $date ) );
		}

		return date( 'Y-m-d', $yesterday );
	}

	/**
	 *
	 * @param integer $laterTime number of days/hours/second that want passed
	 * @param string $unitOfmeasurement days/hours/second etc
	 *
	 * @return date
	 */
	public function getLaterDatetime( $laterTime, $unitOfmeasurement, $date = '' ) {
		if ( $date != '' ) {
			$today = $date;
		} else {
			$today = date( 'Y-m-d H:i:s' );
		}
		$todaytimestamp = strtotime( $today );
		$latertimestamp = strtotime( "+{$laterTime} $unitOfmeasurement", $todaytimestamp );
		$laterdate      = date( 'Y-m-d H:i:s', $latertimestamp );

		return $laterdate;
	}

	/**
	 * Function to lookup a $col
	 *
	 * @param string $model model name
	 * @param integer $id Model PK
	 * @param string $col the required col
	 *
	 * @return mixed
	 */
	public function getColById( $model, $id, $col ) {
		$mod = $model::findOne( $id );
		if ( $mod ) {
			return $mod->$col;
		}
	}

	/**
	 * Function to return date difffernce
	 *
	 * @param date $startdate start date
	 * @param date $enddate end date
	 *
	 * @return integer
	 */
	public function getDateDifference( $startdate, $enddate, $durationUnit = '' ) {
		$datetime1 = new \DateTime( $startdate );
		$datetime2 = new \DateTime( $enddate );
		$interval  = $datetime2->diff( $datetime1 );

		if ( $durationUnit == 'hours' ) {
			$hours = $interval->h;

			return $hours + ( $interval->days * 24 );
		} else {
			return $interval->days;
		}
	}

	/**
	 * Function to get the number of seconds between 2 dates ::Works from dates BTWN 1970 - 2038
	 * @param $startdate start date
	 * @param $enddate end date
	 * @return integer seconds
	 */
	public function getSecondsBetweenDates( $startdate, $enddate ) {
            $seconds = strtotime( $enddate ) - strtotime( $startdate );
            return $seconds;
	}

	

	/**
	 *
	 * @param array $perm_gr permision groups
	 * @param array $perm extra permissions
	 *
	 * @return string username
	 */
	public function getMembers( $perm_gr = [], $perm = []) {
		$membersperm = [];
		if ( isset( Yii::$app->user->identity->email ) ) { //Catch the pointer incase session times out mid-system
			$thisuser = Yii::$app->user->identity->email;
			if ( in_array( '*', $perm_gr ) && $thisuser != "Guest" ) {
				return array( $thisuser );
			} else {
				$loaduser            = Users::find()->where( "email = '{$thisuser}'" )->one();
				$usergrp             = $loaduser->perm_group;
				$usrgrpobj           = PermissionGroup::findOne( $usergrp );
				$groupperm           = explode( ',', $usrgrpobj->defaultPermissions );
				$extuserperm         = $loaduser->extpermission; // To continue
				$extpermarray        = explode( ',', $extuserperm );
				$deniedpermarray     = explode( ',', $loaduser->defaultpermissiondenied );
				$thisuserperms       = array_merge( $extpermarray, $groupperm );
				$thisUserAllowedPerm = array_diff( $thisuserperms, $deniedpermarray );
				//$perm_gr=  explode(',', $perm_gr);

                                foreach ( $perm_gr as $group ) {
                                        $grp         = PermissionGroup::find()->where( "name = '$group'" )->one();
                                        if($grp){
                                            $groupperm   = explode( ',', $grp->defaultPermissions );
                                            $membersperm = array_merge( $membersperm, $groupperm );
                                        }
                                }
				$membersperm = array_merge( $membersperm, $perm );
				$isauth      = array_intersect( $membersperm, $thisUserAllowedPerm );
				if ( $isauth ) {
					return array( $thisuser );
				}
			}
		}

		return array( 'mikiki' );
	}


	/**
	 * Method to get weekdays difference
	 *
	 * @param $currentdate
	 * @param $enddate
	 * @param string $return
	 *
	 * @return int|string
	 */
	public function getWeekdayDifference( $currentdate, $enddate, $return = '' ) {
		if ( $return == '' ) {
			$return = Yii::$app->myhelper->getDaysInMonth( date( "Y-m-d" ) );
		}

		//loop through the dates, from the start date to the end date
		while ( $currentdate <= $enddate ) {
			$timestamp = strtotime( $currentdate );
			//if you encounter a Saturday or Sunday, remove from the total days count
			if ( ( date( 'D', $timestamp ) == 'Sat' ) || ( date( 'D', $timestamp ) == 'Sun' ) ) {
				$return = $return - 1;
			}
			$currentdate = date( 'Y-m-d', strtotime( '+1 day', $timestamp ) );
		} //end date walk loop
		//return the number of working days
		return $return;
	}

	/**
	 * Method to get days in a month 28/29/30 or 31
	 * @return type
	 */
	public function getDaysInMonth( $date ) {
		$month = date( 'm', strtotime( $date ) );
		$year  = date( 'Y', strtotime( $date ) );
		$days  = cal_days_in_month( CAL_GREGORIAN, $month, $year );

		return $days;
	}

	/**
	 * Method to get distance between two points
	 *
	 * @param interger $lat1 Latitude   point 1 (in decimal degrees)
	 * @param integer $lon1 Longitude of point 1 (in decimal degrees)
	 * @param integer $lat2 Latitude  of point 2 (in decimal degrees)
	 * @param integer $lon2 Longitude of point 2 (in decimal degrees)
	 * @param string $unit the unit you desire for results where: 'M' is statute miles (default)       'K' is kilometers  'N' is nautical miles
	 *
	 * @return type
	 */
	public function getDistance( $lat1, $lon1, $lat2, $lon2, $unit ) {
		$theta = $lon1 - $lon2;
		$dist  = sin( deg2rad( $lat1 ) ) * sin( deg2rad( $lat2 ) ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * cos( deg2rad( $theta ) );
		$dist  = acos( $dist );
		$dist  = rad2deg( $dist );
		$miles = $dist * 60 * 1.1515;
		$unit  = strtoupper( $unit );

		if ( $unit == "K" ) {
			return ( $miles * 1.609344 );
		} else if ( $unit == "N" ) {
			return ( $miles * 0.8684 );
		} else {
			return $miles;
		}
	}

	/**
	 * Method to write to file
	 *
	 * @param type $filename
	 * @param type $data
	 */
	public static function writeToFile( $filename, $data ) {
		file_put_contents( $filename, $data, FILE_APPEND );
	}

	/**
	 * return an  array of mumbers
	 *
	 * @param int $last_number
	 * @param bool $time
	 * @param bool $even
	 *
	 * @return array
	 */
	public function getListofNumbers( $last_number = 40, $time = false,$even=false ) {
		$list = [];
		if ( $last_number == 3 || $last_number == 8 ) {
			// eight-used in select button for worker manual hours selection
			$p = 1;
		} else {
			$p = 0;
		}
		for ( $i = $p; $i <= $last_number; $i ++ ) {
			if ( $time ) {
				$list[ $i ] = $i . ":00";
			} else {
				if ($even ){
					if($i % 2 == 0){
						$list[ $i ] = $i;
					}
				}else{
					$list[ $i ] = $i;
				}

			}
		}

		return $list;
	}


	/**
	 * Method to get standard deviation
	 *
	 * @param array $a
	 * @param boolean $sample
	 *
	 * @return boolean
	 */
	function stats_standard_deviation( array $a, $sample = false ) {
		$n = count( $a );
		if ( $n === 0 ) {
			trigger_error( "The array has zero elements", E_USER_WARNING );

			return false;
		}
		if ( $sample && $n === 1 ) {
			trigger_error( "The array has only 1 element", E_USER_WARNING );

			return false;
		}
		$mean  = array_sum( $a ) / $n;
		$carry = 0.0;
		foreach ( $a as $val ) {
			$d     = ( (double) $val ) - $mean;
			$carry += $d * $d;
		};
		if ( $sample ) {
			-- $n;
		}

		return sqrt( $carry / $n );
	}

	/**
	 * function used to validate the phone
	 *
	 * @param integer $phone_number
	 *
	 * @return boolean
	 */
	function validatePhone( $phone_number ) {
            //eliminate every char except 0-9
            $phone_number = preg_replace( "/[^0-9]/", '', $phone_number );
            //eliminate leading 255 if its there
            if( strlen( $phone_number ) == 12 ) {
                $phone_number = preg_replace( "/^255/", '0', $phone_number );
            }
            if(strlen( $phone_number ) == 12 ){
                $phone_number = preg_replace( "/^254/", '0', $phone_number );
            }
            if( strlen( $phone_number ) == 15 ) {
                $phone_number = preg_replace( "/^000255/", '0', $phone_number );
            }
            if(strlen( $phone_number ) == 15 ) {
                $phone_number = preg_replace( "/^000254/", '0', $phone_number );
            }

            if(strlen($phone_number) == 9){ //addition for csv not holding leading zeros
                $phone_number = '0'.$phone_number;
            }
            //if we have 10 digits left, it's probably valid.
            if ( strlen( $phone_number ) == 10 && is_numeric( $phone_number ) ) {
                return true;
            } else {
                return false;
            }
	}

	//generate random password for Eache User

	/**
	 * Method to set XML from an array
	 *
	 * @param array $data array data to convert to XML
	 * @param string $xml_data XML string
	 */
	public function array_to_xml( $data, &$xml_data ) {
		foreach ( $data as $key => $value ) {
			// echo '"\n"';
			if ( is_numeric( $key ) ) {
				$key = 'Customer';
			}
			if ( is_array( $value ) ) {
				$subnode = $xml_data->addChild( $key );
				$this->array_to_xml( $value, $subnode );
			} else {
				$result = $xml_data->addChild( "$key", '' );
				if ( $key == 'Identifier' ) {
					$result->addAttribute( 'IdentifierType', 'MSISDN' );
					$result->addAttribute( 'IdentifierValue', htmlspecialchars( "$value" ) );
				} else {
					$result->addAttribute( 'value', htmlspecialchars( "$value" ) );
				}
			}
		}
	}

	/**
	 * Method to set XML from, raw data formation
	 *
	 * @param array $data array data to convert to XML
	 * @param string $xml_data XML string
	 *
	 * @return string
	 */
	public function array_to_xml_raw( $data, &$xml_data ) {
		$result = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$result .= '<BulkPaymentRequest>' . "\n";
		foreach ( $data as $value ) {
			$result .= '<Customer>' . "\n";
			$result .= '<Identifier IdentifierType="MSISDN" IdentifierValue="' . $value['Identifier'] . '"></Identifier>' . "\n";
			$result .= '<Amount Value="' . $value['Amount'] . '"></Amount>' . "\n";
			$result .= '<Comment Value="' . $value['Comment'] . '"></Comment>' . "\n";
			$result .= '</Customer>' . "\n";
		}
		$result .= '</BulkPaymentRequest>' . "\n";

		return $result;
	}

	/**
	 * Method to get variance
	 */
	public function getVariance() {

	}

	

	/**
	 * @param int $seconds
	 * @param string $start_time
	 * @param string $end_time
	 *
	 * @return string
	 */
	public function secondsToTime( $seconds = 0, $start_time = "", $end_time = "" ) {
		if ( $start_time != "" && $end_time != "" ) {
			$datetime1 = new \DateTime( $start_time );
			$datetime2 = new \DateTime( $end_time );
		} else {
			$datetime1 = new \DateTime( '@0' );
			$datetime2 = new \DateTime( '@' . $seconds );
		}
		$interval = $datetime1->diff( $datetime2 );
		if ( $interval->format( '%a' ) > 0 ) {
			return $interval->format( '%a' ) . " days " . $interval->format( '%h' ) . " hours " . $interval->format( '%i' ) . " minutes " . $interval->format( '%s' ) . " seconds";
		} elseif ( $interval->format( '%h' ) > 0 ) {
			return $interval->format( '%h' ) . " hours " . $interval->format( '%i' ) . " minutes " . $interval->format( '%s' ) . " seconds";
		} elseif ( $interval->format( '%i' ) > 0 ) {
			return $interval->format( '%i' ) . " minutes " . $interval->format( '%s' ) . " seconds";
		} else {
			return $interval->format( '%s' ) . " seconds";
		}
	}

	/**
	 * @param $arr
	 *
	 * @return bool
	 */
	public function isHomogenous( $arr ) {
		$firstValue = current( $arr );
		foreach ( $arr as $val ) {
			if ( $firstValue !== $val ) {
				return false;
			}
		}

		return true;
	}
    public static function curlPost($postData,$headers,$url)
    {
        $ch = curl_init($url);
		curl_setopt_array( $ch, array(
			CURLOPT_POST           => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_POSTFIELDS     =>$postData
		) );
		// Send the request
		$response = curl_exec($ch);
		curl_close($ch);
        return $response;
    }
	/**
	 * Method to get duration of the cache in seconds
	 * @return int
	 */
	public function getDuration() {
		return 5;
	}

	/**
	 * Method to  get duration of the cache in seconds
	 * @return int
	 */
	public function getLongDuration() {
		return 86400; // 24hrs
	}

	/**
	 *
	 * @param type $str
	 *
	 * @return type
	 */
	public static function escape_javascript_string( $str ) {
		// if php supports json_encode, use it (support utf-8)
		if ( function_exists( 'json_encode' ) ) {
			return json_encode( $str );
		}
		// php 5.1 or lower not support json_encode, so use str_replace and addcslashes
		// remove carriage return
		$str = str_replace( "\r", '', (string) $str );
		// escape all characters with ASCII code between 0 and 31
		$str = addcslashes( $str, "\0..\37'\\" );
		// escape double quotes
		$str = str_replace( '"', '\"', $str );
		// replace \n with double quotes
		$str = str_replace( "\n", '\n', $str );

		return "'{$str}'";
	}

	
	/**
	 * Method to do http post using curl
	 *
	 * @param string $url http
	 * @param array $data data in array
	 *
	 * @return type
	 */
	public function httpPost( $url, $data ) {
		//Initiate cURL.
		$ch = curl_init( $url );
		//Encode the array into JSON.
		$jsonDataEncoded = json_encode( $data );

		//Tell cURL that we want to send a POST request.
		curl_setopt( $ch, CURLOPT_POST, 1 );

		//Attach our encoded JSON string to the POST fields.
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonDataEncoded );

		//Set the content type to application/json
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );

		//Enable return as string
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		//Execute the request
		$result = curl_exec( $ch );

		return $result;
	}


	/**
	 * get the difference between two dates except sundays and saturdays
	 * @return int
	 */
	public static function getDateDiff( $period, $days ) {
		// best stored as array, so you can add more than one
		$holidays = array( '2017-09-07' );
		foreach ( $period as $dt ) {

			$curr = $dt->format( 'D' );

			// substract if Saturday or Sunday
			if ( $curr == 'Sun' || $curr == 'Sat' ) {
				$days --;
			} // (optional) for the updated question
			elseif ( in_array( $dt->format( 'Y-m-d' ), $holidays ) ) {
				$days --;
			}
		}

		return $days;
	}

	public function dd($v) {
		\yii\helpers\VarDumper::dump($v, 10, true);
		exit();
	}
	public static function getAll($sql)
    {
        return \Yii::$app->db->createCommand($sql)->queryAll(); 
    }
    public static function getOne($sql)
    {
        return \Yii::$app->db->createCommand($sql)->queryOne(); 
    }
    public static function runQuery($sql)
    {
        return \Yii::$app->db->createCommand($sql)->execute(); 
    }
    /**
     * Method to get dataprovider
     * @param object $searchModel search model
     * @return type
     */
    public  function getdataprovider($searchModel){
        $today           = date( 'Y-m-d' );
        $dateSixWeeksAgo = date( 'Y-m-d', strtotime( '-42 day' ) );
        if ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'daily' ) {
                $dataProvider = $searchModel->search( Yii::$app->request->queryParams, true, false );
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'monthly' ) {
                $dataProvider = $searchModel->search( Yii::$app->request->queryParams, false, true );
        } elseif ( isset( $_GET['criterion'] ) && $_GET['criterion'] == 'range' ) {
                if ( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
                        $to       = $_GET['to'];
                        $from     = $_GET['from'];
                        $date1    = strtotime( $to );
                        $date2    = strtotime( $from );
                        if ( $date1 < $date2 ) {
                                Yii::$app->session->setFlash('error', 'Error: start date should be before the end date' );
                        }
                        $dataProvider = $searchModel->search( Yii::$app->request->queryParams, false, false, $from, $to );
                } else {
                        $dataProvider = $searchModel->search( Yii::$app->request->queryParams, false, false, $dateSixWeeksAgo, $today );
                }
        } else {
                $dataProvider = $searchModel->search( Yii::$app->request->queryParams, true, false );
        }
        return $dataProvider;
    }
	public static function drawMessage($first_name)
	{
		$message="";
		$hostname = gethostname();
		
		if ( ( in_array($hostname,[COMP21_COKE]))) {
			$message = "$first_name, Umeingia Draw! Endelea Kushiriki, Waweza tunukiwa, PB 5668989 Ksh 100, T&C apply. Customer care  0719034035";
		}
		else if ( ( in_array($hostname, [COMP21_NET]))) {
			$message = "$first_name, Umeingia Draw! Endelea Kushiriki, Waweza tunukiwa, PB 4044043 Ksh 100, T&C apply. Customer care  0719034036";
		}
		else if ( ( in_array($hostname, [COMP21_DEV]))) {
			$message = "$first_name, Umeingia Draw! Endelea Kushiriki, Waweza tunukiwa, PB 321321 Ksh 100, T&C apply. Customer care  0719034037";		}
		 else {
			$message = "$first_name, Umeingia Draw! Endelea Kushiriki, Waweza tunukiwa, PB 5668989 Ksh 100, T&C apply. Customer care  0719034035";
		 }
		 return $message;
	}
	public static function invalidAmountMessage($first_name)
	{
		$message="";
		$hostname = gethostname();
		if ( ( in_array($hostname, [COMP21_COKE]))) {
			$message = "$first_name, Kushiriki kwenye draw ni shilingi mia moja tu,Waweza tunukiwa, PB 5668989 Ksh 100, T&C apply. Customer care  0719034035";
		}
		else if ( ( in_array($hostname, [COMP21_NET]))) {
			$message = "$first_name, Kushiriki kwenye draw ni shilingi mia moja tu,Waweza tunukiwa, PB 4044043 Ksh 100, T&C apply. Customer care  0719034036";		}
		else if ( ( in_array($hostname, [COMP21_DEV]))) {
			$message = "$first_name, Kushiriki kwenye draw ni shilingi mia moja tu,Waweza tunukiwa, PB 321321 Ksh 100, T&C apply. Customer care  0719034037";		}
		 else {
			$message = "$first_name, Kushiriki kwenye draw ni shilingi mia moja tu,Waweza tunukiwa, PB 5668989 Ksh 100, T&C apply. Customer care  0719034035";
		 }
		 return $message;
	}
	public static function winningMessage($transaction_history,$show_prize,$station_name)
	{
		$message="";
		$hostname = gethostname();
		if ( ( in_array($hostname, [COMP21_COKE]))) {
			$message="CONGRATULATIONS ".$transaction_history['reference_name']."!, You have won ".$show_prize['name']." worth Kshs ".$show_prize['amount']." from $station_name. For more details C/Care 0719034035.T&C apply. 20% Tax Charged.";
		}
		else if ( ( in_array($hostname, [COMP21_NET]))) {
			$message="CONGRATULATIONS ".$transaction_history['reference_name']."!, You have won ".$show_prize['name']." worth Kshs ".$show_prize['amount']." from $station_name. For more details C/Care 0719034036.T&C apply. 20% Tax Charged.";
			}
		else if ( ( in_array($hostname, [COMP21_DEV]))) {
			$message="CONGRATULATIONS ".$transaction_history['reference_name']."!, You have won ".$show_prize['name']." worth Kshs ".$show_prize['amount']." from $station_name. For more details C/Care 0719034037.T&C apply. 20% Tax Charged.";		}
		 else {
			$message="CONGRATULATIONS ".$transaction_history['reference_name']."!, You have won ".$show_prize['name']." worth Kshs ".$show_prize['amount']." from $station_name. For more details C/Care 0719034035.T&C apply. 20% Tax Charged.";
		 }
		 return $message;
	}
        
        public static function formatHour($hr){
            if($hr < 10)
            {
                return '0'.$hr;
            }
            else
            {
                return $hr;
            }
        }
			/**
	 * Method to send sms notification to the outbox
	 *
	 * @param string $name notification name
	 * @param integer $receiver receiver mobile number
	 * @param array $variables array maping the variable to replace in the sms templat
	 */
	public static function setSms( $name, $receiver, $variables = [], $sender = SENDER_NAME,$station_id ) {
		$outbox = new Outbox();
		if ( $receiver != "") {
			$temp = Template::getTemplate($name);
			$data = [
				'sender'       => $sender,
				'receiver'     => $receiver,
				'created_date' => date( 'Y-m-d H:i:s' ),
				'category'     =>$temp->id,
				'station_id'     =>$station_id,
				'status'       => 0
			];
			$temp=$temp->message;
			switch ( $name ) {
				case 'validDraw':
					$toreplace       = [ "[customer_name]" ];
					$data['message'] = str_replace( $toreplace, $variables, $temp );
					break;
				case 'validDrawEntry':
					$toreplace       = ["[customer_name]","[entryNumber]"];
					$data['message'] = str_replace( $toreplace, $variables, $temp );
					break;

				case 'invalidDrawAmount':
					$toreplace       = [ "[customer_name]" ];
					$data['message'] = str_replace( $toreplace, $variables, $temp );
					break;
				case 'winningMessage':
					$toreplace       = [ "[customer_name]", "[prize_name]","[station_name]" ];
					$data['message'] = str_replace( $toreplace, $variables, $temp );
					break;
				case 'rewardPlayer':
					$toreplace       = ["[amount]" ];
					$data['message'] = str_replace( $toreplace, $variables, $temp );
					break;
				case 'bonus':
						$toreplace       = ["[amount]","[station_name]" ];
						$data['message'] = str_replace( $toreplace, $variables, $temp );
						break;	
				case 'yangaDraw':
						$toreplace       = [];
						$data['message'] = str_replace( $toreplace, $variables, $temp );
						break;
				case '881/EBONY':
						$toreplace       =["[entryNumber]"];
						$data['message'] = str_replace( $toreplace, $variables, $temp );
						break;
				case 'Noti/883':
						$toreplace       = ["[entryNumber]"];
						$data['message'] = str_replace( $toreplace, $variables, $temp );
						break;
				case 'BONGO/333':
						$toreplace       = ["[entryNumber]"];
						$data['message'] = str_replace( $toreplace, $variables, $temp );
						break;
				case '255/KONDE':
							$toreplace       = ["[entryNumber]"];
							$data['message'] = str_replace( $toreplace, $variables, $temp );
							break;
				default:
					$data['message'] = isset( $variables['message'] ) ? $variables['message'] : '';
					break;
			}
			$outbox->id=Uuid::generate()->string;
			if($station_id=="f50262b0-e4c3-11ed-a1ec-1972e3e43059")
            {
				if($name=='validDrawEntry')
				{
					$data['message']='HONGERA MWANANCHI!Umeingia droo ya YANGA unaweza kushinda zawadi lukuki&TIKETI kusafiri na TIMU.Cheza Zaidi USHINDE.Vigezo&Masharti kuzingatiwa.Piga 0659070070';
				}
				if($name=='winningMessage')
				{
					$data['message']='HONGERA MWANANCHI! Umejishindia TIKETI kutoka YANGA.Kwa maelezo zaidi piga simu 0659070070. Endelea Kucheza USHINDE ZAIDI.Vigezo na masharti kuzingatiwa';
				}
				
			}
			$outbox->attributes = $data;
			$outbox->save(false);
			//Outbox::sendOutbox($outbox->id);
			Yii::$app->queue->push(new OutboxJob(['id'=>$outbox->id]));
		}
	}

	public static function curlGet($url)
	{
			$ch = curl_init($url);
			curl_setopt_array( $ch, array(
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_RETURNTRANSFER => true
			) );
			// Send the request
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
	}
	public static function sendTzSms($msisdn,$message,$sender_name,$channel,$reference)
	{
		$url="http://213.136.80.136:9090/bulksms?message=".urlencode($message)."&msisdn=$msisdn&channel=$channel&shortcode=".urlencode($sender_name)."&relay_id=".SMS_RELAY_ID."&reference=".$reference."&username=".SMS_USERNAME."&password=".SMS_PASSWORD;
		return Myhelper::curlGet($url);
	}
		/**
	 * function to get the channel;
	 *
	 * @param type $phone_number
	 *
	 * @return int
	 */
	public static function getSmsChannel( $phone_number ) {
		$phone_number = preg_replace( "/[^0-9]/", '', $phone_number );
		if ( strlen( $phone_number ) == 12 ) {
			$phone_number = preg_replace( "/^255/", '0', $phone_number );
		}
		if ( strlen( $phone_number ) == 12 ) {
			$phone_number = preg_replace( "/^254/", '0', $phone_number );
		}
		if ( strlen( $phone_number ) == 15 ) {
			$phone_number = preg_replace( "/^000255/", '0', $phone_number );
		}
		if ( strlen( $phone_number ) == 15 ) {
			$phone_number = preg_replace( "/^000254/", '0', $phone_number );
		}
		$operator_prefix = substr( $phone_number, 0, 3 );
		$operator        = "";
		if ( $operator_prefix == "076" || $operator_prefix == "075" || $operator_prefix == "074" ) {
			$operator = "TANZANIA.VODACOM.RELAY";
		} elseif ( $operator_prefix == "068" || $operator_prefix == "069" || $operator_prefix == "078" ) {
			$operator = "TANZANIA.AIRTEL.RELAY";
		}
		 elseif ( $operator_prefix == "065" || $operator_prefix == "067" || $operator_prefix == "071" ) {
			$operator = "TANZANIA.TIGO.RELAY";
		} 
		elseif ( $operator_prefix == "062" || $operator_prefix == "061" ) {
			$operator = "TANZANIA.HALOTEL.RELAY";
		} 

		return $operator;
	}
	public static function getOperator($phone_number) {
		$operator_prefix = substr( $phone_number, 0, 5 );
		$operator        = "";
		 if (in_array($operator_prefix,["25576","25575","25574"]))
		{
			$operator="vodacom";
		}
		else if (in_array($operator_prefix,["25568","25569","25578"]))
		{
			$operator="airtel";
		}
		else if (in_array($operator_prefix,["25565","25567","25571"]))
		{
			$operator="tigo";
		}
		else if (in_array($operator_prefix,["25562","25561"]))
		{
			$operator="halotel";
		}
		else
		{
			$operator="notset";
		}

		return $operator;
	}
	public static function checkLocalToken() 
	{
        $header  = getallheaders();
		$token=trim($header['Authorization']);
        if ( isset( $token) ) {
            if ($token==DEPOSIT_AUTHORIZATION ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
	}
        
        public function getStations() {
           $return = [];
           $data = \app\models\StationManagementStations::find()
                    ->select('station_id')
                    ->where(['station_management_id'=> \Yii::$app->user->identity->id])
                    ->all();
            foreach ($data as $value) {
                $return[]=$value->station_id;
            }
            return $return;
        }
		public  function  isStationManager()
		{
			if(isset(\Yii::$app->user->identity) && in_array(\Yii::$app->user->identity->perm_group, [4,5])){
                return true;
            }else{
                return false;
            }
		}
		public static function formatAirtelDate($str)
		{
			$year=substr($str,2,2);
			$month=substr($str,4,2);
			$day=substr($str,6,2);
			$hour=substr($str,9,2);
			$minute=substr($str,11,2);
			$datetime="20$year-$month-$day $hour:$minute:00";
			return $datetime;
		}
		/**
	 * Method to calc time diff btn dates
	 * @param string $start_date
	 * @param string $end_date
	 * @param int $diff
	 * @return int time_diff
	 */
	public static function getTimeDiff($start_date,$end_date,$diff='',$sign=true){
		$first_date = new DateTime($start_date);
		$second_date = new DateTime($end_date);
		$interval = $first_date->diff($second_date);
		$days_passed = $interval->format('%a');
		$hours_diff = $interval->format('%H');
		$minutes_diff = $interval->format('%I');
		$seconds_diff = $interval->format('%S');
		$time_diff='';
		switch ($diff) {
			case 'seconds':
				$total_minutes = (($days_passed*24 + $hours_diff) * 60 + $minutes_diff);
				$time_diff = $total_minutes*60 + $seconds_diff;
				break;
			case 'minutes':
				$time_diff = (($days_passed*24 + $hours_diff) * 60 + $minutes_diff);
				break;
			case 'hours':
				$time_diff = ($days_passed*24 + $hours_diff);
				break;
			case 'days':
				$time_diff = $interval->format('%a');
				break;      
			default:
				# code...
				break;
		}
		if((strtotime($start_date) > strtotime($end_date)) && $sign==true){
			return $time_diff*(-1);
		}else{
			return $time_diff;
		}
	}	
	public static function compareCode($a,$b)
	{
		$a=strtolower($a);
		$b=strtolower($b);
		if(substr($a,0,3)==substr($b,0,3))
		{
			return true;
		}
		else if(substr($a,0,3)==substr($b,-3,3))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public static function removeSpecialChars($str) {
		$cleanStr = '';
		$allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ';
	
		for ($i = 0; $i < strlen($str); $i++) {
			if (strpos($allowedChars, $str[$i]) !== false) {
				$cleanStr .= $str[$i];
			}
		}
	
		return $cleanStr;
	}	
	public static function cleanNumber($str) {
		$cleanStr = '';
		$allowedChars = '0123456789';
	
		for ($i = 0; $i < strlen($str); $i++) {
			if (strpos($allowedChars, $str[$i]) !== false) {
				$cleanStr .= $str[$i];
			}
		}
	
		return $cleanStr;
	}	
}

