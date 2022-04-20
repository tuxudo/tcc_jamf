<?php
/**
 * tcc_jamf module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Tcc_jamf_controller extends Module_controller
{
    public function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }

    /**
    * Default method
    *
    * @author AvB
    **/
    public function index()
    {
        echo "You've loaded the tcc_jamf module!";
    }

    /**
     * Get TCC service data for scroll widget
     *
     * @return void
     * @author tuxudo
     **/
    public function get_scroll_widget($service)
    {
        $service = preg_replace("/[^A-Za-z0-9_\-]]/", '', $service);
        
        $sql = "SELECT COUNT(CASE WHEN `service` <> '' AND `service` IS NOT NULL THEN 1 END) AS count, service, client 
                FROM tcc
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                AND `service` = '".$service."'
                GROUP BY client
                ORDER BY count DESC";

        $out = [];
        $queryobj = new Tcc_model;
        foreach ($queryobj->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->service = $obj->service ? $obj->service : 'Unknown';
                $out[] = $obj;
            }
        }

        jsonView($out);
    }

    /**
    * Retrieve data in json format
    *
    * @return void
    * @author tuxudo
    **/
    public function get_tab_data($serial_number = '')
    {
        $serial_number = preg_replace("/[^A-Za-z0-9_\-]]/", '', $serial_number);

        $sql = "SELECT service, client, allowed, prompt_count, indirect_object_identifier, last_modified, dbpath 
                        FROM tcc 
                        WHERE serial_number = '$serial_number'";
        
        $obj = new View();
        $queryobj = new Tcc_model();
        $tcc_tab = $queryobj->query($sql);
        $obj->view('json', array('msg' => current(array('msg' => $tcc_tab)))); 
    }
} // END class Tcc_jamf_controller
