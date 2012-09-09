<?php

/**
 * ownCloud
 *
 * @author Patrik Karisch
 * @copyright 2012 Patrik Karisch <patrik.karisch@abimus.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class OC_User_phpbb3 extends OC_User_Backend {
	protected $phpbb3_db_host;
	protected $phpbb3_db_name;
	protected $phpbb3_db_user;
	protected $phpbb3_db_password;
	protected $phpbb3_db_prefix;
	protected $db;
	protected $db_conn;

	function __construct() {
		$this->db_conn = false;
		$this->phpbb3_db_host = OC_Appconfig::getValue('user_phpbb3', 'phpbb3_db_host','');
		$this->phpbb3_db_name = OC_Appconfig::getValue('user_phpbb3', 'phpbb3_db_name','');
		$this->phpbb3_db_user = OC_Appconfig::getValue('user_phpbb3', 'phpbb3_db_user','');
		$this->phpbb3_db_password = OC_Appconfig::getValue('user_phpbb3', 'phpbb3_db_password','');
		$this->phpbb3_db_prefix = OC_Appconfig::getValue('user_phpbb3', 'phpbb3_db_prefix','');

		$errorlevel = error_reporting();
		error_reporting($errorlevel & ~E_WARNING);
		$this->db = new mysqli($this->phpbb3_db_host, $this->phpbb3_db_user, $this->phpbb3_db_password, $this->phpbb3_db_name);
		error_reporting($errorlevel);
		if ($this->db->connect_errno) {
			OC_Log::write('OC_User_phpbb3',
					'OC_User_phpbb3, Failed to connect to phpbb3 database: ' . $this->db->connect_error,
					OC_Log::ERROR);
			return false;
		}
		$this->db_conn = true;
		$this->phpbb3_db_prefix = $this->db->real_escape_string($this->phpbb3_db_prefix);
	}

	/**
	 * @brief Set email address
	 * @param $uid The username
	 */
	private function setEmail($uid) {
		if (!$this->db_conn) {
			return false;
		}

		$q = 'SELECT user_email FROM '. $this->phpbb3_db_prefix .'users WHERE username = "'. $this->db->real_escape_string($uid) .'" AND user_type = 0 OR user_type = 3';
		$result = $this->db->query($q);
		$email = $result->fetch_assoc();
		$email = $email['user_email'];
		OC_Preferences::setValue($uid, 'settings', 'email', $email);
	}

	/**
	 * @brief Check if the password is correct
	 * @param $uid The username
	 * @param $password The password
	 * @returns true/false
	 */
	public function checkPassword($uid, $password){
		if (!$this->db_conn) {
			return false;
		}

		$query = 'SELECT username FROM '. $this->phpbb3_db_prefix .'users WHERE username = "' . $this->db->real_escape_string($uid) . '"';
		$query .= ' AND user_password = "' . md5($this->db->real_escape_string($password)) . '" AND user_type = 0 OR user_type = 3';
		$result = $this->db->query($query);
		$row = $result->fetch_assoc();

		if ($row) {
			$this->setEmail($uid);
			return $row['username'];
		}
		return false;
	}

	/**
	 * @brief Get a list of all users
	 * @returns array with all uids
	 *
	 * Get a list of all users
	 */
	public function getUsers() {
		$users = array();
		if (!$this->db_conn) {
			return $users;
		}

		$q = 'SELECT username FROM '. $this->phpbb3_db_prefix .'users WHERE user_type = 0 OR user_type = 3';
		$result = $this->db->query($q);
		while ($row = $result->fetch_assoc()) {
			if(!empty($row['username'])) {
				$users[] = $row['username'];
			}
		}
		sort($users);
		return $users;
	}

	/**
	 * @brief check if a user exists
	 * @param string $uid the username
	 * @return boolean
	 */
	public function userExists($uid) {
		if (!$this->db_conn) {
			return false;
		}

		$q = 'SELECT username FROM '. $this->phpbb3_db_prefix .'users WHERE username = "'. $this->db->real_escape_string($uid) .'"  AND user_type = 0 OR user_type = 3';
		$result = $this->db->query($q);
		return $result->num_rows > 0;
	}
}
