<?php
namespace core\services;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * LDAP interface to connect to a LDAP server
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
class LDAP extends Service
{

    private $service_Settings;

    private $bo_debug = false;

    private $obj_connection = null;

    /**
     * PHP 5 constructor
     *
     * @param core\services\Settings $service_Settings
     *            The settings service
     */
    public function __construct(\core\services\Settings $service_Settings)
    {
        $this->service_Settings = $service_Settings;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->unbind();
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * Simulates the LDAP connection for unit-testing
     * DO NOT USE this in production
     */
    public function activateDebug()
    {
        $this->bo_debug = true;
    }

    /**
     * Checks if LDAP is supported on the server
     *
     * @return bool if LDAP is supported
     */
    public function isSupported()
    {
        if (! function_exists('ldap_connect')) {
            return false;
        }
        
        return true;
    }

    /**
     * Connects to the default LDAP server
     *
     * @param String $s_username            
     * @param String $s_password            
     * @throws LdapConnectionException LDAP is not activated in the settings
     * @throws LdapException the login details are invalid
     */
    public function bind($s_username, $s_password)
    {
        if ($this->service_Settings->get('settings/LDAP/active') != '1') {
            throw new \LdapConnectionException('LDAP is not activated in settings');
        }
        
        $s_server = $this->service_Settings->get('settings/LDAP/server');
        $i_port = $this->service_Settings->get('settings/LDAP/port');
        $i_version = $this->service_Settings->get('settings/LDAP/version');
        
        $this->bindManual($s_server, $i_port, $s_username, $s_password, $i_version);
    }

    /**
     * Connects to the given LDAP server
     *
     * @param String $s_server
     *            host
     * @param int $i_port
     *            number
     * @param String $s_username            
     * @param string $s_password            
     * @param int $i_version
     *            version (2|3), default 3
     * @throws LdapException LDAP is not supported on the server or connection error
     * @throws LdapConnectionException the login details are invalid
     */
    public function bindManual($s_server, $i_port, $s_username, $s_password, $i_version = 3)
    {
        $this->unbind();
        
        if (! $this->isSupported()) {
            throw new \LdapException('LDAP is not supported on this PHP installation.');
        }
        
        if (! in_array($i_version, array(
            2,
            3
        ))) {
            $i_version = 3;
        }
        
        $obj_connection = ldap_connect($s_server, $i_port);
        if ($obj_connection === false) {
            throw new \LdapException('LDAP connection to server ' . $s_server . ' on port ' . $i_port . ' failed.');
        }
        
        ldap_set_option($obj_connection, LDAP_OPT_PROTOCOL_VERSION, $i_version);
        ldap_set_option($obj_connection, LDAP_OPT_REFERRALS, 0);
        
        if( $this->bo_debug ){
            $this->obj_connection = $obj_connection;
            return;
        }
        
        if (! @ldap_bind($obj_connection, $s_username, $s_password)) {
            throw new \LdapConnectionException('Could not connect to server '.$s_server.' on port '.$i_port.' with geven username and password is.');
        }
        
        $this->obj_connection = $obj_connection;
    }

    /**
     * Closes the connection to the current LDAP server
     */
    public function unbind()
    {
        if (! is_null($this->obj_connection)) {
            ldap_unbind($this->obj_connection);
            
            $this->obj_connection = null;
        }
    }

    /**
     * Adds a item to the LDAP server
     *
     * @param String $s_name            
     * @param array $a_data            
     * @throws LdapConnectionException no connection is present.
     * @throws LdapException adding the item failed
     */
    public function add($s_name, $a_data)
    {
        $this->checkConnection();
        
        if (!@ ldap_add($this->obj_connection, $s_name, $a_data)) {
            throw new \LdapException('Adding ' . $s_name . ' failed : ' . ldap_error($this->obj_connection));
        }
    }

    /**
     * Deletes a item from the LDAP server
     *
     * @param String $s_name            
     * @throws LdapConnectionException no connection is present.
     * @throws LdapException deleting the item failed
     */
    public function delete($s_name)
    {
        $this->checkConnection();
        
        if (!@ ldap_delete($this->obj_connection, $s_name)) {
            throw new \LdapException('Deleting ' . $s_name . ' failed : ' . ldap_error($this->obj_connection));
        }
    }

    /**
     * Searches on the baseDN on the LDAP server
     *
     * @param String $s_baseDN
     *            to search on
     * @param String $s_filter            
     * @param array $a_attributes
     *            attributes, default no filtering
     * @param int $i_attributesOnly
     *            1 for only attribute types, default attribute types and attribute values
     * @param int $i_sizelimit
     *            of results, default no limit
     * @param int $i_timelimit
     *            for searching, default no limit
     * @throws LdapConnectionException no connection is present.
     * @throws LdapException searching failes
     * @return array results
     */
    public function search($s_baseDN, $s_filter, $a_attributes = array(), $i_attributesOnly = 0, $i_sizelimit = 0, $i_timelimit = 0)
    {
        $this->checkConnection();
        
        $search = @ldap_search($this->obj_connection, $s_baseDN, $s_filter, $a_attributes, $i_attributesOnly, $i_sizelimit, $i_timelimit);
        if ($search === false) {
            throw new \LdapException('Searching on ' . $s_filter . ' failed : ' . ldap_error($this->obj_connection));
        }
        
        $a_data = ldap_get_entries($s_baseDN, $search);
        return $a_data;
    }

    /**
     * Reads the baseDN on the LDAP server
     *
     * @param String $s_baseDN
     *            to read on
     * @param String $s_filter            
     * @param array $a_attributes
     *            attributes, default no filtering
     * @param int $i_attributesOnly
     *            1 for only attribute types, default attribute types and attribute values
     * @param int $i_sizelimit
     *            of results, default no limit
     * @param int $i_timelimit
     *            for searching, default no limit
     * @throws LdapConnectionException no connection is present.
     * @throws LdapException reading failes
     * @return array item
     */
    public function readItem($s_baseDN, $s_filter, $a_attributes = array(), $i_attributesOnly = 0, $i_sizelimit = 0, $i_timelimit = 0)
    {
        $this->checkConnection();
        
        $item = @ldap_read($this->obj_connection, $s_baseDN, $s_filter, $a_attributes, $i_attributesOnly, $i_sizelimit, $i_timelimit);
        if ($item === false) {
            throw new \LdapException('Reading on ' . $s_filter . ' failed : ' . ldap_error($this->obj_connection));
        }
        
        $a_data = ldap_get_entries($s_baseDN, $item);
        return $a_data;
    }

    /**
     * Modifies a item on the LDAP server
     *
     * @param String $s_name            
     * @param array $a_data            
     * @throws LdapConnectionException no connection is present.
     * @throws LdapException modifying the item failed
     */
    public function modify($s_name, $a_data)
    {
        $this->checkConnection();
        
        if (! @ldap_modify($this->obj_connection, $s_name, $a_data)) {
            throw new \LdapException('Modifying ' . $s_name . ' failed : ' . ldap_error($this->obj_connection));
        }
    }

    /**
     * Renames a item to a new name
     *
     * @param String $s_oldName
     *            name
     * @param String $s_newName
     *            name
     * @param String $s_newParent
     *            parent name
     * @param bool $bo_deleteOldRDN
     *            false to preserve the old RDN data
     * @throws LdapConnectionException no connection is present.
     * @throws LdapException renaming the item failed
     */
    public function rename($s_oldName, $s_newName, $s_newParent, $bo_deleteOldRDN = true)
    {
        $this->checkConnection();
        
        if (! ldap_rename($this->obj_connection, $s_oldName, $s_newName, $s_newParent, $bo_deleteOldRDN)) {
            throw new \LdapException('Renaming ' . $s_oldName . ' to ' . $s_newName . ' failed : ' . ldap_error($this->obj_connection));
        }
    }

    /**
     * Checks if the login to the LDAP is correct
     *
     * @param String $s_server
     *            host
     * @param int $i_port
     *            number
     * @param String $s_username            
     * @param String $s_password            
     * @throws bool if the login is correct
     */
    public function checkLogin($s_server, $i_port, $s_username, $s_password)
    {
        try {
            $this->bindManual($s_server, $i_port, $s_username, $s_password);
            
            $this->unbind();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Checks if a connection is present
     *
     * @throws LdapConnectionException no connection is present
     */
    private function checkConnection()
    {
        if (is_null($this->obj_connection)) {
            throw new \LdapConnectionException('No connection to a LDAP server.');
        }
    }
}