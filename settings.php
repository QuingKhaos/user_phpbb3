<?php

/**
 * ownCloud - user_redmine
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
$params = array(
		'phpbb3_db_host',
		'phpbb3_db_user',
		'phpbb3_db_password',
		'phpbb3_db_name',
		'phpbb3_db_prefix'
);

if ($_POST) {
	foreach($params as $param){
		if(isset($_POST[$param])){
			OC_Appconfig::setValue('user_phpbb3', $param, $_POST[$param]);
		}
	}
}

// fill template
$tmpl = new OC_Template('user_phpbb3', 'settings');
foreach($params as $param){
	$value = OC_Appconfig::getValue('user_phpbb3', $param,'');
	$tmpl->assign($param, $value);
}

return $tmpl->fetchPage();
