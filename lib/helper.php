<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace OCA\user_phpbb3\lib;

class Helper {
  private static $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

  /**
  * Check for correct password
  *
  * @param string $password The password in plain text
  * @param string $hash The stored password hash
  *
  * @return bool Returns true if the password is correct, false if not.
  */
  public static function phpbb_check_hash($password, $hash)
  {
    if (strlen($hash) == 34) {
      return (self::crypt($password, $hash) === $hash) ? true : false;
    }

    return (md5($password) === $hash) ? true : false;
  }

  /**
  * Encode hash
  */
  private static function encode64($input, $count) {
    $output = '';
    $i = 0;

    do {
      $value = ord($input[$i++]);
      $output .= static::$itoa64[$value & 0x3f];

      if ($i < $count) $value |= ord($input[$i]) << 8;
      $output .= static::$itoa64[($value >> 6) & 0x3f];
      if ($i++ >= $count) break;

      if ($i < $count) $value |= ord($input[$i]) << 16;
      $output .= static::$itoa64[($value >> 12) & 0x3f];

      if ($i++ >= $count) break;
      $output .= static::$itoa64[($value >> 18) & 0x3f];
    } while ($i < $count);

    return $output;
  }

  /**
  * The crypt function/replacement
  */
  private static function crypt($password, $password_hash) {
    $output = '*';

    // Check for correct hash
    if (! in_array(substr($password_hash, 0, 3), array('$H$', '$P$'))) {
      return $output;
    }

    $count_log2 = strpos(static::$itoa64, $password_hash[3]);

    if ($count_log2 < 7 || $count_log2 > 30) return $output;

    $count = 1 << $count_log2;
    $salt = substr($password_hash, 4, 8);

    if (strlen($salt) != 8) return $output;

    /**
    * We're kind of forced to use MD5 here since it's the only
    * cryptographic primitive available in all versions of PHP
    * currently in use.  To implement our own low-level crypto
    * in PHP would result in much worse performance and
    * consequently in lower iteration counts and hashes that are
    * quicker to crack (by non-PHP code).
    */
    if (PHP_VERSION >= 5) {
      $hash = md5($salt . $password, true);
      do {
        $hash = md5($hash . $password, true);
      } while (--$count);
    } else {
      $hash = pack('H*', md5($salt . $password));
      do {
        $hash = pack('H*', md5($hash . $password));
      } while (--$count);
    }

    $output = substr($password_hash, 0, 12);
    $output .= self::encode64($hash, 16, static::$itoa64);

    return $output;
  }
}