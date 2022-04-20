TCC Jamf Module
==============

Reports the TCC configuration on the client Mac by using a Jamf Extension Attribute to generate the file. This enables the module to use Jamf's preexisting full disk access to access the TCC database file.

The included `TCC_Jamf_Extension_Attribute.xml` Extension Attribute can be directly uploaded into Jamf or you can create an Extension Attribute using the included `TCC_Jamf_Extension_Attribute_Script.py` script. 


Table Schema
----

* dbpath - VARCHAR(255) - Path of TCC.db entry is from
* service - VARCHAR(255) - Service name
* client - VARCHAR(255) - Bundle ID of client
* client_type - integer - Client type
* allowed - boolean - If access is allowed
* prompt_count - INT(11) - Count of times prompted for access
* indirect_object_identifier - VARCHAR(255) - Paired client ID
* last_modified - big int - Timestamp of last modification of entry
