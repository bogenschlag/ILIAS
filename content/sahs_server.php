<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* scorm learning module presentation script
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

chdir("..");

// debug
/*
$fp=fopen("./content/scorm.log", "a+");
foreach ($HTTP_POST_VARS as $key=>$value)
	fputs($fp, "HTTP_POST_VARS[$key] = $value <br>";
foreach ($HTTP_GET_VARS as $key=>$value)
	fputs($fp, "HTTP_GET_VARS[$key] = $value <br>";
fclose($fp);	
*/
		
$cmd = ($_GET["cmd"] == "")
	? $_POST["cmd"]
	: $_GET["cmd"];

	$ref_id=$_GET["ref_id"];
	
	//get type of cbt
	if (!empty($ref_id)) {

		require_once "./include/inc.header.php";
			
		//read by ref_id
		$q = "SELECT type FROM object_data od, object_reference oref WHERE oref.ref_id=$ref_id AND oref.obj_id=od.obj_id";
		$lm_set = $ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);
		$type=$lm_rec["type"];	

	} else {	

		//ensure HACP
		$requiredKeys=array("command", "version", "session_id");
		if (count(array_diff ($requiredKeys, array_keys(array_change_key_case($HTTP_POST_VARS, CASE_LOWER))))==0) {
			
			//now we need to get a connection to the database and global params
			//but that doesnt work because of missing logindata of the contentserver
			//require_once "./include/inc.header.php";
			
			//highly insecure
			$param=urldecode($HTTP_POST_VARS["session_id"]);
			if (!empty($param) && substr_count($param, "_")==2) {
				list($session_id, $ref_id, $obj_id)=explode("_",$param);
	
				session_id($session_id);
				require_once "./include/inc.header.php";
				
				$type="hlm";
			}
		}
	}
	
	switch ($type) {
		case "slm":
					//SCORM
					require_once "./content/classes/SCORM/class.ilObjSCORMTracking.php";
					$track = new ilObjSCORMTracking();
					$track->$cmd();
					break;
		case "alm":
					//AICC
					require_once "./content/classes/AICC/class.ilObjAICCTracking.php";
					$track = new ilObjAICCTracking();
					$track->$cmd();
					break;
		case "hlm":
					//HACP
					require_once "./content/classes/HACP/class.ilObjHACPTracking.php";
					$track = new ilObjHACPTracking($ref_id, $obj_id);
					//$track->$cmd();
					break;
		default:
					//unknown type
					$fp=fopen("./content/scorm.log", "a+");
					fputs($fp, "unknown type >$type< in sahs_server\n");	
					foreach ($HTTP_POST_VARS as $k=>$v)
						fputs($fp, "HTTP_POST_VARS[$k]=$v \n");	
					fclose($fp);	
	}

exit;

?>
