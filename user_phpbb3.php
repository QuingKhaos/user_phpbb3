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

		$result = $this->db->query('SELECT user_email FROM '. $this->phpbb3_db_prefix .'users WHERE username = "'. $this->db->real_escape_string($uid) .'"');
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
		$query .= ' AND user_password = "' . $this->phpbb_hash($this->db->real_escape_string($password)) . '"';
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

		$result = $this->db->query('SELECT username FROM '. $this->phpbb3_db_prefix .'users');
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

		$result = $this->db->query('SELECT username FROM '. $this->phpbb3_db_prefix .'users WHERE username = "'. $this->db->real_escape_string($uid) .'"');
		return $result->num_rows > 0;
	}

	/**
	 *
	 * @version Version 0.1 / slightly modified for phpBB 3.0.x (using $H$ as hash type identifier)
	 *
	 * Portable PHP password hashing framework.
	 *
	 * Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
	 * the public domain.
	 *
	 * There's absolutely no warranty.
	 *
	 * The homepage URL for this framework is:
	 *
	 *	http://www.openwall.com/phpass/
	 *
	 * Please be sure to update the Version line if you edit this file in any way.
	 * It is suggested that you leave the main version number intact, but indicate
	 * your project name (after the slash) and add your own revision information.
	 *
	 * Please do not change the "private" password hashing method implemented in
	 * here, thereby making your hashes incompatible.  However, if you must, please
	 * change the hash type identifier (the "$P$") to something different.
	 *
	 * Obviously, since this code is in the public domain, the above are not
	 * requirements (there can be none), but merely suggestions.
	 *
	 *
	 * Hash the password
	 */
	private function phpbb_hash($password)
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$random_state = md5(microtime());
		$random = '';
		$count = 6;

		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($random) < $count)
		{
			$random = '';

			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5($random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}

		$hash = $this->hash_crypt($password, $this->hash_gensalt($random, $itoa64), $itoa64);

		if (strlen($hash) == 34)
		{
			return $hash;
		}

		return md5($password);
	}

	/**
	 * Generate salt for hash generation
	 */
	private function hash_gensalt($input, &$itoa64, $iteration_count_log2 = 6)
	{
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
		{
			$iteration_count_log2 = 8;
		}

		$output = '$H$';
		$output .= $itoa64[min($iteration_count_log2 + ((PHP_VERSION >= 5) ? 5 : 3), 30)];
		$output .= $this->hash_encode64($input, 6, $itoa64);

		return $output;
	}

	/**
	 * The crypt function/replacement
	 */
	private function hash_crypt($password, $setting, &$itoa64)
	{
		$output = '*';

		// Check for correct hash
		if (substr($setting, 0, 3) != '$H$' && substr($setting, 0, 3) != '$P$')
		{
			return $output;
		}

		$count_log2 = strpos($itoa64, $setting[3]);

		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);

		if (strlen($salt) != 8)
		{
			return $output;
		}

		/**
		 * We're kind of forced to use MD5 here since it's the only
		 * cryptographic primitive available in all versions of PHP
		 * currently in use.  To implement our own low-level crypto
		 * in PHP would result in much worse performance and
		 * consequently in lower iteration counts and hashes that are
		 * quicker to crack (by non-PHP code).
		 */
		if (PHP_VERSION >= 5)
		{
			$hash = md5($salt . $password, true);
			do
			{
				$hash = md5($hash . $password, true);
			}
			while (--$count);
		}
		else
		{
			$hash = pack('H*', md5($salt . $password));
			do
			{
				$hash = pack('H*', md5($hash . $password));
			}
			while (--$count);
		}

		$output = substr($setting, 0, 12);
		$output .= $this->hash_encode64($hash, 16, $itoa64);

		return $output;
	}

	/**
	 * Encode hash
	 */
	private function hash_encode64($input, $count, &$itoa64)
	{
		$output = '';
		$i = 0;

		do
		{
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}

			$output .= $itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}

			$output .= $itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			$output .= $itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}
}
