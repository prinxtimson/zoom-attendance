<?php

/*

Plugin Name: Zoom Attendance

Description: This is a custom Zoom Meeting attendance.

Version: 1.0.0

Author: Tabtech

Author URI: #

Text Domain: zoom-attendance

Depends: notification

*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'notification/load.php' );

use Firebase\JWT\JWT;

use BracketSpace\Notification\Register;

function zoom_attendance_menu()
{
  add_menu_page(
    __( 'Zoom Attendance', 'Zoom Attendance' ),
    __( 'Zoom Attendance', 'All Attendance' ),
    'manage_options',
    'zoom-attendance',
    'za_admin_page_html',
    'dashicons-schedule',
    3
  );
  
}

// function zoom_attendance_settings_page()
// {
//     add_submenu_page(
//         'zoom-attendance',
//         'Settings',
//         'Settings',
//         'manage_options',
//         'zoom-attendance-settings',
//         'zoom_attendance_settings_page_html'
//     );
// }

add_action( 'admin_menu', 'zoom_attendance_menu' );
//add_action( 'admin_menu', 'zoom_attendance_settings_page' );

function za_admin_page_html() 
{
  $tab = isset($_GET['tab']) ? $_GET['tab'] : null;

  switch($tab) :
    case 'participants' :
      return zoom_attendance_settings_page_html();
    default : 
      return zoom_attendance_page_html();
    endswitch;
  }

function zoom_attendance_page_html()
{
  $wp_list_table = new Zoom_Attendance_Table();
  $wp_list_table->prepare_items();
  ?>
<div class="wrap">
  <h1>Zoom Meeting</h1>
  <div class="zoom_dateinput_field">
    <form action="" class="zvc_accounts_reports_check_form" method="POST">
      <label>From</label>
      <input name="za_from" class="zoom_account_datepicker" type="date" />
      <label>To</label>
      <input name="za_to" class="zoom_account_datepicker" type="date" /> <input type="submit" name="submit_btn"
        value="Check">
    </form>
  </div>
  <div>
    <?php $wp_list_table->display(); ?>
  </div>
</div>
<?php
}

function zoom_attendance_settings_page_html()
{
  $wp_list_table = new Zoom_Participants_Table();
  $wp_list_table->prepare_items();
  
  ?>
<div class="wrap">
  <h1>Zoom Meeting Participants</h1>
  <div>
    <div style="margin-top: 20px;">
      <form action="" class="zvc_accounts_reports_check_form" method="GET">
        <input name="page" class="zoom_account_datepicker" type="hidden" value="zoom-attendance" />
        <input name="tab" class="zoom_account_datepicker" type="hidden" value="participants" />
        <input name="za_meetingId" class="zoom_account_datepicker" type="hidden"
          value="<?php echo($_GET['za_meetingId']); ?>" />
        <input type="radio" id="unique_users" name="unique" value="unique_users">
        <label for="unique">Show unique participants</label><br>

        <input type="radio" id="users" name="unique" value="">
        <label for="css">Show participants</label><br>

        <input type="submit" class="zoom_account_datepicker" name="submit_btn" value="Get Participants" />
      </form>
    </div>
    <?php $wp_list_table->display(); ?>
  </div>
</div>
<?php
}

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//if ( ! class_exists( 'Zoom_Attendance_Table' ) ) {
  class Zoom_Attendance_Table extends WP_List_Table {

    		/**
		 * Hold my instance
		 *
		 * @var
		 */
		// protected static $_instance;

    // 		/**
		//  * Create only one instance so that it may not Repeat
		//  *
		//  * @since 2.0.0
		//  */
		// public static function instance() {
		// 	if ( is_null( self::$_instance ) ) {
		// 		self::$_instance = new self();
		// 	}

		// 	return self::$_instance;
		// }
/*
 * Constructor, we override the parent to pass our own arguments
 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
 */
function __construct() {
  parent::__construct( array(
 'singular'=> 'report', //Singular label
 'plural' => 'reports', //plural label, also this well be one of the table css class
 'ajax'   => false //We won't support Ajax for this table
 ) );
}

/**
* Add extra markup in the toolbars before or after the list
* @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
*
*function extra_tablenav( $which ) {
*   if ( $which == "top" ){
*  //The code that goes before the table is here
 * echo"Complaint Table";
  *}
  *if ( $which == "bottom" ){
  *The code that goes after the table is there
  *echo" table";
 * }
*}
* Define the columns that are going to be used in the table
* @return array $columns, the array of columns to use with the table
*/

private function get_account_report_html() {
  $return_result = false;
  if ( isset( $_POST['za_from'] ) && isset( $_POST['za_to'] ) ) {
    $zoom_account_from = $_POST['za_from'];
    $zoom_account_to   = $_POST['za_to'];
  
    $zoom_attendance_api = new Zoom_Attendance_Api();
    // if ( $zoom_account_from == null || $zoom_account_to == null ) {
    //   $return_result = __( "The fields cannot be Empty !!", "video-conferencing-with-zoom-api" );
    // } else {
      $return_result = json_decode( $zoom_attendance_api->getAccountReport($zoom_account_from, $zoom_account_to));
      
      //var_dump($return_result);
      
    //}
  }

  return $return_result;
}

public function get_columns() {
  return $columns = array(
      'cb'            => '<input type="checkbox">',
     'col_meeting_topic' => __('Topic'),
     'col_meeting_meeting_id' => __('Meeting ID'),
     'col_meeting_user_name' => __('User Name'),
     'col_meeting_user_email'=>__('User Email'),
     'col_meeting_start_time' => __('Start Time'),
     'col_meeting_end_time' => __('End Time'),
     'col_meeting_duration' => __('Duration (Minutes)'),
     'col_meeting_participants' => __('Participants'),
     'col_meeting_source' => __('Source'),
  );
}


/**
* Decide which columns to activate the sorting functionality on
* @return array $sortable, the array of columns that can be sorted by the user
*/
public function get_sortable_columns() {
  return $sortable = array(
  'col_meta_value'=> array('meta_value', true)
  );
}


/**
* Prepare the table with different parameters, pagination, columns and table elements
*/
public function prepare_items() {
  //global $wpdb, $_wp_column_headers;
  //$screen = get_current_screen();

    $result = $this->get_account_report_html();

      $this->items = $result;

      //if($this->items){
  /* -- Pagination parameters -- */
      //Number of elements in your table?
      $totalitems = $this->items->total_records; //return the total number of affected rows
      //How many to display per page?
      $perpage = $this->items->page_size;
      //Which page is this?
      $paged = $this->items->page_count;
      //Page Number
      //if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; } //How many pages do we have in total?

      $totalpages = $this->items->page_count; //adjust the query to take pagination into account

      // if(!empty($paged) && !empty($perpage)){ $offset=($paged-1)*$perpage; $query.=' LIMIT '.(int)$offset.','.(int)$perpage; }

      /* -- Register the pagination -- */ 
      $this->set_pagination_args( array(
      "total_items" => $totalitems,
      "total_pages" => $totalpages,
      "per_page" => $perpage,
      ) );
      //The pagination links are automatically built according to those parameters

      /* -- Register the Columns -- */
      $columns = $this->get_columns();
      $hidden = array();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array($columns, $hidden, $sortable);
      // }else {
      //   $this->items->meetings = array();
      // }

}


/**
* Display the rows of records in the table
* @return string, echo the markup of the rows
*/
public function display_rows() {

  //Get the records registered in the prepare_items method
  $records = $this->items->meetings;
  
  $meetings = $records;

  // if(!empty($records)){

  //     $id = 0;
  //     $count = 0;
  //     $temp_arr = array();
  //     $arr_len = sizeof($records);
      
  //     foreach($records as $rec){
          
  //         $count++;
          
  //         if($count === $arr_len){
              
  //             $temp_arr[$rec->meta_key] = $rec->meta_value;
  //             if(!empty($temp_arr)){array_push($meetings, $temp_arr);}
              
  //         }elseif($rec->entry_id !== $id){

  //             $id = $rec->entry_id;

  //             if(!empty($temp_arr)){array_push($meetings, $temp_arr);}

  //             $temp_arr = array(
  //                 'id' => $id,
  //                 $rec->meta_key => $rec->meta_value
  //             );

  //         }else {

  //             $temp_arr[$rec->meta_key] = $rec->meta_value;
  //         }
  //     }
      
  // }

  //Get the columns registered in the get_columns and get_sortable_columns methods
  list( $columns, $hidden ) = $this->get_column_info();

  //Loop for each record
  if(!empty($meetings)){
      foreach($meetings as $meeting){

  //Open the line
      echo '<tr id="record_'.$meeting->id.'">';
      foreach ( $columns as $column_name => $column_display_name ) {
  
          $classes = "$column_name column-$column_name";
          // if ( $primary === $column_name ) {
          //     $classes .= ' has-row-actions column-primary';
          // }

          if ( in_array( $column_name, $hidden ) ) {
              $classes .= ' hidden';
          }

          // Comments column uses HTML in the display name with screen reader text.
          // Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
          $data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

          $attributes = "class='$classes' $data";
  
          //Display the cell
          switch ( $column_name ) {
              case "cb":  echo '<td '.$attributes.'><input type="checkbox"></td>';   break;
              case "col_meeting_topic":  echo '<td '.$attributes.'>'.stripslashes($meeting->topic).'</td>';   break;
              case "col_meeting_meeting_id": echo '<td '.$attributes.'>'.stripslashes($meeting->id).'</td>'; break;
              case "col_meeting_user_name": echo '<td '.$attributes.'>'.stripslashes($meeting->user_name).'</td>'; break;
              case "col_meeting_user_email": echo '<td '.$attributes.'>'.stripslashes($meeting->user_email).'</td>'; break;
              case "col_meeting_start_time": echo '<td '.$attributes.'>'.stripslashes(date('M j, Y h:i:s A', strtotime($meeting->start_time))).'</td>'; break;
              case "col_meeting_end_time": echo '<td '.$attributes.'>'.stripslashes(date('M j, Y h:i:s A', strtotime($meeting->end_time))).'</td>'; break;
              case "col_meeting_duration": echo '<td '.$attributes.'>'.stripslashes($meeting->duration).'</td>'; break;
              case "col_meeting_participants": echo '<td '.$attributes.'><a href="?page=zoom-attendance&tab=participants&za_meetingId='.$meeting->id.'" >'.stripslashes($meeting->participants_count).'</a></td>'; break;
              case "col_meeting_source": echo '<td '.$attributes.'>'.stripslashes($meeting->source).'</td>'; break;
          }
      }
      
      

    //Close the line
    echo '</tr>';
    }}
  }
}

class Zoom_Participants_Table extends WP_List_Table {


  /*
  * Constructor, we override the parent to pass our own arguments
  * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
  */
  function __construct() {
    parent::__construct( array(
    'singular'=> 'report', //Singular label
    'plural' => 'reports', //plural label, also this well be one of the table css class
    'ajax'   => false //We won't support Ajax for this table
    ) );
  }

  private function get_participants_report_html() {
    $return_result = false;
    if ( isset( $_GET['za_meetingId'] ) ) {
      $zoom_meeting_id = $_GET['za_meetingId'];

      $zoom_attendance_api = new Zoom_Attendance_Api();
    
      $return_result = json_decode( $zoom_attendance_api->getParticipantsReport($zoom_meeting_id));

      if (isset( $_GET['unique'] ) && $_GET['unique'] === 'unique_users' ){
        $participants = array();

        $len = count($return_result->participants);

        for ($x = 0; $x < $len; $x++) {
          $current = $return_result->participants[$x];

          $key = array_search($current->name, array_column($participants, 'name'));

          if($key) {         
            $total = $participants[$key]['duration'] + $current->duration;

            $participants[$key]['duration'] = $total;
          } else {
            array_push($participants, array(
              'id' => $current->id,
              'name' => $current->name,
              'user_email' => $current->user_email,
              'duration' => $current->duration
            ));
          }
        }

        $return_result->participants = $participants;

      }
      
    }

    return $return_result;
  }

  public function get_columns() {
    if (isset( $_GET['unique'] ) && $_GET['unique'] === 'unique_users' ){
      return $columns = array(
        'col_meeting_name' => __('Name'),
        'col_meeting_user_email'=>__('User Email'),
        'col_meeting_duration' => __('Duration (Minutes)'),
        );
    }
    return $columns = array(
    'col_meeting_name' => __('Name'),
    'col_meeting_user_email'=>__('User Email'),
    'col_meeting_join_time' => __('Join Time'),
    'col_meeting_leave_time' => __('Leave Time'),
    'col_meeting_duration' => __('Duration (Minutes)'),
    );
  }


/**
* Decide which columns to activate the sorting functionality on
* @return array $sortable, the array of columns that can be sorted by the user
*/
  public function get_sortable_columns() {
    return $sortable = array(
    'col_meta_value'=> array('meta_value', true)
    );
  }


/**
* Prepare the table with different parameters, pagination, columns and table elements
*/
  public function prepare_items() {

    $this->items = $this->get_participants_report_html(); 

    /* -- Pagination parameters -- */
    //Number of elements in your table?
    $totalitems = $this->items->total_records; //return the total number of affected rows
    //How many to display per page?
    $perpage = $this->items->page_size;
    //Which page is this?
    $paged = $this->items->page_count;
    //Page Number

    $totalpages = $this->items->page_count; //adjust the query to take pagination into account

    /* -- Register the pagination -- */ 
    $this->set_pagination_args( array(
    "total_items" => $totalitems,
    "total_pages" => $totalpages,
    "per_page" => $perpage,
    ) );
    //The pagination links are automatically built according to those parameters

    /* -- Register the Columns -- */
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);

  }


  /**
  * Display the rows of records in the table
  * @return string, echo the markup of the rows
  */
  public function display_rows() {

    //Get the records registered in the prepare_items method
    $records = $this->items->participants;

    $meetings = $records;

    //Get the columns registered in the get_columns and get_sortable_columns methods
    list( $columns, $hidden ) = $this->get_column_info();

    if(isset( $_GET['unique'] ) && $_GET['unique'] === 'unique_users' ){
//Loop for each record
if(!empty($meetings)){
  foreach($meetings as $meeting){
    //var_dump($meeting);

    //Open the line
    echo '<tr id="record_'.$meeting['id'].'">';
      foreach ( $columns as $column_name => $column_display_name ) {

          $classes = "$column_name column-$column_name";

          if ( in_array( $column_name, $hidden ) ) {
              $classes .= ' hidden';
          }

          // Comments column uses HTML in the display name with screen reader text.
          // Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
          $data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

          $attributes = "class='$classes' $data";

          //Display the cell
          switch ( $column_name ) {
              case "col_meeting_name":  echo '<td '.$attributes.'>'.stripslashes($meeting['name']).'</td>';   break;
              case "col_meeting_user_email": echo '<td '.$attributes.'>'.stripslashes($meeting['user_email']).'</td>'; break;
              case "col_meeting_duration": echo '<td '.$attributes.'>'.stripslashes(ceil($meeting['duration']/60)).'</td>'; break;
          }
      }

    //Close the line
    echo '</tr>';
  }
}
    }else {
      //Loop for each record
    if(!empty($meetings)){
      foreach($meetings as $meeting){

        //Open the line
        echo '<tr id="record_'.$meeting->id.'">';
          foreach ( $columns as $column_name => $column_display_name ) {

              $classes = "$column_name column-$column_name";

              if ( in_array( $column_name, $hidden ) ) {
                  $classes .= ' hidden';
              }

              // Comments column uses HTML in the display name with screen reader text.
              // Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
              $data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

              $attributes = "class='$classes' $data";

              //Display the cell
              switch ( $column_name ) {
                  case "col_meeting_name":  echo '<td '.$attributes.'>'.stripslashes($meeting->name).'</td>';   break;
                  case "col_meeting_user_email": echo '<td '.$attributes.'>'.stripslashes($meeting->user_email).'</td>'; break;
                  case "col_meeting_join_time": echo '<td '.$attributes.'>'.stripslashes(date('M j, Y h:i:s A', strtotime($meeting->join_time))).'</td>'; break;
                  case "col_meeting_leave_time": echo '<td '.$attributes.'>'.stripslashes(date('M j, Y h:i:s A', strtotime($meeting->leave_time))).'</td>'; break;
                  case "col_meeting_duration": echo '<td '.$attributes.'>'.stripslashes(ceil($meeting->duration/60)).'</td>'; break;
              }
          }

        //Close the line
        echo '</tr>';
      }
    }
    }

  }
}

class Zoom_Attendance_Api {

  /**
   * Zoom API KEY
   *
   * @var
   */
  public $zoom_api_key;

  /**
   * Zoom API Secret
   *
   * @var
   */
  public $zoom_api_secret;

  /**
   * Hold my instance
   *
   * @var
   */
  protected static $_instance;

  /**
   * API endpoint base
   *
   * @var string
   */
  private $api_url = 'https://api.zoom.us/v2/';

  /**
   * Create only one instance so that it may not Repeat
   *
   * @since 2.0.0
   */
  // public static function instance() {
  //   if ( is_null( self::$_instance ) ) {
  //     self::$_instance = new self();
  //   }

  //   return self::$_instance;
  // }

  /**
   * Zoom_Video_Conferencing_Api constructor.
   *
   * @param $zoom_api_key
   * @param $zoom_api_secret
   */
  public function __construct( $zoom_api_key = 'M5Sli5_fQfWHJyjYK3m4ng', $zoom_api_secret = 'QhfKz2KFHtgt8rqRJrMPbXPNvCBijwqJ9q8h' ) {
    $this->zoom_api_key    = $zoom_api_key;
    $this->zoom_api_secret = $zoom_api_secret;
  }

  /**
   * Send request to API
   *
   * @param        $calledFunction
   * @param        $data
   * @param string $request
   *
   * @return array|bool|string|WP_Error
   */
  protected function sendRequest( $calledFunction, $data, $request = "GET" ) {
    $request_url = $this->api_url . $calledFunction;
    $args        = array(
      'headers' => array(
        'Authorization' => 'Bearer '. $this->generateJWTKey(),
        'Content-Type'  => 'application/json'
      )
    );

    if ( $request == "GET" ) {
      $args['body'] = ! empty( $data ) ? $data : array();
      $response     = wp_remote_get( $request_url, $args );
    } else if ( $request == "DELETE" ) {
      $args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
      $args['method'] = "DELETE";
      $response       = wp_remote_request( $request_url, $args );
    } else if ( $request == "PATCH" ) {
      $args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
      $args['method'] = "PATCH";
      $response       = wp_remote_request( $request_url, $args );
    } else if ( $request == "PUT" ) {
      $args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
      $args['method'] = "PUT";
      $response       = wp_remote_request( $request_url, $args );
    } else {
      $args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
      $args['method'] = "POST";
      $response       = wp_remote_post( $request_url, $args );
    }

    $response = wp_remote_retrieve_body( $response );
    /*dump($response);
    die;*/

    if ( ! $response ) {
      return false;
    }

    return $response;
  }

  //function to generate JWT
  private function generateJWTKey() {
  	$key    = $this->zoom_api_key;
  	$secret = $this->zoom_api_secret;

  	$token = array(
  		"iss" => $key,
  		"exp" => time() + 3600 //60 seconds as suggested
  	);

  	return JWT::encode( $token, $secret );
  }

  /**
   * Creates a User
   *
   * @param $postedData
   *
   * @return array|bool|string
   */
  public function getAccountReport( $zoom_account_from, $zoom_account_to ) {
    $getAccountReportArray              = array();
    $getAccountReportArray['from']      = $zoom_account_from;
    $getAccountReportArray['to']        = $zoom_account_to;
    $getAccountReportArray['page_size'] = 300;
    // $getAccountReportArray              = apply_filters( 'vczapi_getAccountReport', $getAccountReportArray );
  
    return $this->sendRequest( 'report/users/info@tritekconsulting.co.uk/meetings', $getAccountReportArray, "GET" );
  }

  public function getParticipantsReport($meetingID)
  {
    $getAccountReportArray              = array();

    return $this->sendRequest( '/report/meetings/'.$meetingID.'/participants', $getAccountReportArray, "GET" );
  }

}

add_action('notification/init', function() {

  require_once('custom_trigger.php');
  
  Register::trigger(new ZoomAttendanceTrigger);
  // Register::trigger(new MeetingEndedTrigger);
});

function zoom_meeting_ended($request)
{
  if ($request->event === 'meeting.ended'){
    $zoom_attendance_api = new Zoom_Attendance_Api();

    $payload = $request->payload;

    $absentees = array();
    
    $result = json_decode( $zoom_attendance_api->getParticipantsReport($payload->object->id));

    $subscribers = get_users( array( 'role__in' => array( 'subscriber' ) ) );

    $admin = get_users( array( 'role__in' => array( 'author' ) ) );

    foreach($subscribers as $user){
      $key = array_search($user->user_email , array_column($result->participants, 'user_email'));

      if(! $key){
        array_push($absentees, $user);

        $postarr = array(
          'post_content' => array(
            'name' => $user->display_name,
            'meeting_title' => $payload->object->topic,
            'duration' => 0,
            'meeting_start_time' => $payload->object->start_time,
            'meeting_end_time' => $payload->object->end_time
          ),
          'post_author' => $user->id,
          'post_type' => 'zoom_attendance',
          'post_title' => 'Zoom Attendance',
          'comment_status' => 'closed'
        );

        wp_insert_post($postarr);

        wp_mail($user->user_email, 'Meeting Missed', 'Your have missed '.$payload->object->topic . ' which was held on '. stripslashes(date('M j, Y h:i:s A', strtotime($payload->object->start_time))).'.');
      }else {
        $duration = 0;

        foreach($$result->participants as $participant){
          if($participant->user_email === $user->user_email) {
            $duration = $duration + $participant->duration;
          }
        }

        $postarr = array(
          'post_content' => array(
            'name' => $user->display_name,
            'meeting_title' => $payload->object->topic,
            'duration' => $duration,
            'start_time' => $payload->object->start_time,
            'end_time' => $payload->object->end_time
          ),
          'post_author' => $user->id,
          'post_type' => 'zoom_attendance',
          'post_title' => 'Zoom Attendance',
          'comment_status' => 'closed'
        );

        wp_insert_post($postarr);
      }
    }

    $emails = array();

    foreach($admin as $user){
      array_push($emails, $user->user_email);
    }
  }
  
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'zoom-attandance/v1', '/meeting-started', array(
    'methods' => 'POST',
    'callback' => 'zoom_meeting_started',
  ) );
} );

function zoom_meeting_started($request)
{
  if ($request->event === 'meeting.started'){
    do_action( 'zoom_meeting_started', $request );
  }
  
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'zoom-attandance/v1', '/meeting-ended', array(
    'methods' => 'POST',
    'callback' => 'zoom_meeting_ended',
  ) );
} );

add_action( 'init', 'wpdocs_add_custom_shortcode' );
 
function wpdocs_add_custom_shortcode() {
    add_shortcode( 'za_table', 'za_table_func' );
}

function za_table_func()
{
  $args = array(
    'author' => get_current_user_id(),
    'post_type' => 'zoom_attendance'
  );

  $posts = new WP_Query($args);

  if ( $posts->have_posts() ) {
    echo '<div class="wrap"> 
            <div id="primary" class="content-area">
              <table>
                <tr>
                  <th>
                    Name
                  </th>
                  <th>
                    Training/Meeting
                  </th>
                  <th>
                    Start Time
                  </th>
                  <th>
                    End Time
                  </th>
                  <th>
                    Duration
                  </th>
                  <th>
                    Remark
                  </th>
                </tr>';
    while ( $posts->have_posts() ) {
        $posts->the_post();
        $content = the_content();
        echo '<tr>' . '<td>' . $content->name . '</td>' . '<td>' . $content->meeting_title . '</td>' . '<td>' . stripslashes(date('M j, Y h:i:s A', strtotime($content->start_time))) . '</td>' . '<td>' . stripslashes(date('M j, Y h:i:s A', strtotime($content->end_time))) . '</td>' . '<td>' . $content->duration/60 . '</td>' . '<td>' . 'remark' . '</td>' . '</tr>';
    }
    echo '</table></div></div>';

    wp_reset_postdata();
  } else {
    echo '<p>No data found</p>';
  }
  //return (
   ?>
<div>

</div>
<?php
  //);
}