<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Legacy_Cert_Table extends WP_List_Table {

    function extra_tablenav( $which ) {
        if ( $which == "top" ){
        //The code that goes before the table is here
        echo "<strong>Note: You can search by Name or Username</strong>";
    }
    }
    function __construct() {
       parent::__construct( array(
      'singular' => 'wp_list_leg_cert', //Singular label
      'plural' => 'wp_list_leg_certs', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    }

    function column_default($item, $column_name){
        switch($column_name){
            case 'cert_id':
            case 'cert_fname':
            case 'cert_lname':
            case 'cert_username':
            case 'cert_credits':
            case 'cert_event':
            case 'cert_start':
            case 'cert_venue':
            case 'cert_city':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_cert_id($item){

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['cert_id'],
            /*$2%s*/ $item['ID']
        );
    }

    function get_columns() {
        $columns = array(
            'cert_id' => __('ID', true),
            'cert_fname' => __('First Name', false),
            'cert_lname' => __('Last Name', false),
            'cert_username' => __('Username', false),
            'cert_credits' => __('CE Credits', false),
            'cert_event' => __('Event', false),
            'cert_start' => __('Event Date', false),
            'cert_venue' => __('Venue', false),
            'cert_city' => __('City', false)
       );
        return $columns;
    }

    function get_sortable_columns() {
    $sortable_columns = array(
        'cert_id' => array('credits_id', true),
        'cert_fname'=> array('first_name', false),
        'cert_lname'=> array('last_name', false),
        'cert_username'=> array('username', false),
        'cert_event' => array('details', false),
        'cert_start' => array('start_date', false),
    );
    return $sortable_columns;
}

function prepare_items() {
    global $wpdb;

    $per_page = 50;
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);

    $table  = $wpdb->prefix . 'ce_credits';
//$conditions = "\"SELECT * FROM {$table}\"";

    $data = $wpdb->get_results("SELECT * FROM {$table}");

    /* If the value is not NULL, do a search for it. */
    $search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
    $do_search = ( $search ) ? $wpdb->prepare( " WHERE `full_name` LIKE '%%%s%%' OR `username` LIKE '%%%s%%' ", $search ) : '';
    $sql_results = $wpdb->get_results( "SELECT $sql_select FROM {$table} WHERE $do_search " );

    if( $search != NULL ){

        // Trim Search Term
        $search = trim($search);

        /* Notice how you can search multiple columns for your search term easily, and return one data set */
        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE `first_name` LIKE '%%%s%%' OR `last_name` LIKE '%%%s%%' OR `username` LIKE '%%%s%%'", $search, $search));

    }
    $current_page = $this->get_pagenum();

    $total_items = count($data);

    $data = array_slice($data,(($current_page-1)*$per_page),$per_page);


   /* -- Fetch the items -- */
    $this->items = $data;

    $this->set_pagination_args( array(
        'total_items' => $total_items,                  //WE have to calculate the total number of items
        'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
        'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
    ) );


}
    function display_rows() {
   //Get the records registered in the prepare_items method
   $records = $this->items;
   //Get the columns registered in the get_columns and get_sortable_columns methods
   list( $columns ) = $this->get_column_info();
   //Loop for each record
   if(!empty($records)){foreach($records as $rec){
      //Open the line
        echo '<tr id="record_' . $rec->credits_id . '">';
      foreach ( $columns as $column_name => $column_display_name ) {

         //Display the cell
         switch ( $column_name ) {
            case "cert_id":  echo '<td>' . $rec->credits_id . '</td>';   break;
            case "cert_fname": echo '<td>' . $rec->first_name . '</td>'; break;
            case "cert_lname": echo '<td>' . $rec->last_name . '</td>'; break;
            case "cert_username": echo '<td>' . $rec->username . '</td>'; break;
            case "cert_credits": echo '<td>' . $rec->credits . '</td>'; break;
            case "cert_event": echo '<td>' . $rec->details . '</td>'; break;
            case "cert_start": echo '<td>' . $rec->start_date . '</td>'; break;
            case "cert_venue": echo '<td>' . $rec->location . '</td>'; break;
            case "cert_city": echo '<td>' . $rec->city . '</td>'; break;
         }
      }

      //Close the line
      echo'</tr>';
   }}
}
}
    //Create an instance of our package class...
    $legCertTable = new Legacy_Cert_Table();
    //Fetch, prepare, sort, and filter our data...
        if( isset($_POST['s']) ){
                $legCertTable->prepare_items($_POST['s']);
        } else {
                $legCertTable->prepare_items();
        }
    $legCertTable->prepare_items();

?>
<div>
<form method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
  <?php $legCertTable->search_box('Search', 'cert-search');
    foreach ($_GET as $key => $value) { echo("<input type='hidden' name='$key' value='$value' />"); }
    ?>
</form>
</div>
<div>
    <form id="cert-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $legCertTable->display() ?>
        </form>
</div>
<!-- / .padding -->
