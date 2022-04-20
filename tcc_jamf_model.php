<?php

use CFPropertyList\CFPropertyList;

class Tcc_jamf_model extends \Model
{
    public function __construct($serial = '')
    {
        parent::__construct('id', 'tcc'); // Primary key, tablename
        $this->rs['id'] = '';
        $this->rs['serial_number'] = $serial;
        $this->rs['dbpath'] = null;
        $this->rs['service'] = null;
        $this->rs['client'] = null;
        $this->rs['client_type'] = null;
        $this->rs['allowed'] = null;
        $this->rs['prompt_count'] = null;
        $this->rs['indirect_object_identifier'] = null;
        $this->rs['last_modified'] = null;
    }


    // ------------------------------------------------------------------------
    /**
     * Process data sent by postflight
     *
     * @param string data
     *
     **/
    public function process($data)
    {
        // If data is empty, echo out error
        if (! $data) {
            echo ("Error Processing tcc module: No data found");
        } else {
            
            // Delete previous entries
            $this->deleteWhere('serial_number=?', $this->serial_number);

            // Process incoming tcc.plist
            $parser = new CFPropertyList();
            $parser->parse($data, CFPropertyList::FORMAT_XML);
            $plist = $parser->toArray();
            
            foreach ($plist as $entry){
                foreach (array('dbpath', 'service', 'client', 'client_type', 'allowed', 'prompt_count', 'indirect_object_identifier', 'last_modified') as $item) {
                    
                    // Only process if there is a client entry
                    if(!$entry['client']){
                        continue;
                    }
                    
                    // If key does not exist in $entry, null it
                    if (( ! array_key_exists($item, $entry) || $entry[$item] == '') && $entry[$item] !== 0) {
                        $this->$item = null;
                    // Set the db fields to be the same as those in the TCC database
                    } else {
                        $this->$item = $entry[$item];
                    }
                }

                // Save the data, taste the data
                $this->id = '';
                $this->save();
            }
        }
    }
}