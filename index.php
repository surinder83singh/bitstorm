<?php 
/*
 * Bitstorm 2 - A small and fast Bittorrent tracker
 * Copyright 2011 Inpun LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*************************
 ** Configuration start **
 *************************/

//phpinfo();

//die();

//MySQL details
include "config.php";

/***********************
 ** Configuration end **
 ***********************/

//Use the correct content-type
header("Content-type: text/html");
?>
<html>
<head>
<style type="text/css">
body, input, textarea { font-family: "Fira Sans","Source Sans Pro",Helvetica,Arial,sans-serif; font-weight: 400;}
table.db-table { border-right: 1px solid #ccc; border-bottom: 1px solid #ccc; margin: 0 auto;}
table.db-table th {background: #eee;padding: 5px;border-left: 1px solid #ccc;border-top: 1px solid #ccc;}
table.db-table td {padding: 5px;border-left: 1px solid #ccc;border-top: 1px solid #ccc; text-align: right;}
</style>
</head>
<body>
<?php

//Connect to the MySQL server
$mysqli = @mysqli_connect(__DB_SERVER, __DB_USERNAME, __DB_PASSWORD, __DB_DATABASE) or die('Database connection failed');

//Select the database
//@mysql_select_db(__DB_DATABASE) or die('Unable to select database');

$q = mysqli_query($mysqli, 'SELECT torrent_id, SUM(uploaded) as uploaded, SUM(downloaded) as downloaded '
		. 'FROM '
		.' (SELECT torrent_id, uploaded, downloaded, MAX(attempt), peer_id FROM peer_torrent '
		. 'GROUP BY torrent_id, peer_id) as X JOIN peer ON X.peer_id = peer.id '
		. 'GROUP BY torrent_id LIMIT 1000') or die(mysqli_error($mysqli));

if(mysqli_num_rows($q)) {
	echo '<table cellpadding="0" cellspacing="0" class="db-table">';
	echo '<tr><th>Torrent ID</th><th>Uploaded</th><th>Downloaded</th></tr>';
	while($r = mysqli_fetch_row($q)) {
		echo '<tr>';
		echo '<td>',$r[0],'</td>';
		echo '<td>',formatBytes($r[1]),'</td>';
		echo '<td>',formatBytes($r[2]),'</td>';
		echo '</tr>';
	}
	echo '</table><br />';
}
function formatBytes($size) {
	if ($size==0) {
		return "0";
	}
	$suffixes = array ('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$base = log($size, 1024);
	$s = pow(1024, $base - floor($base));
	$precision = max(0, 1-floor(log($s, 10)));
	
	return sprintf("%.".$precision."f%s", $s, $suffixes[floor($base)]);
}
?>
</body>
</html>
