<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
*
 ********************************************************************************/

/** Class to retreive all the Parent Groups of the specified Group
 *
 */
require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/GetParentGroups.php');

class GetUserGroups { 

	var $user_groups=Array();
	//var $userRole='';

	/** to get all the parent vtiger_groups of the specified group
	 * @params $groupId --> Group Id :: Type Integer
         * @returns updates the parent group in the varibale $parent_groups of the class
         */
	function getAllUserGroups($userid)
	{
		global $adb,$log, $table_prefix;
		$log->debug("Entering getAllUserGroups(".$userid.") method...");
		//Retreiving from the user2grouptable
		$query="select * from ".$table_prefix."_users2group where userid=?";
        $result = $adb->pquery($query, array($userid));
		$num_rows=$adb->num_rows($result);
		for($i=0;$i<$num_rows;$i++)
		{
			$now_group_id=$adb->query_result($result,$i,'groupid');
			if(! in_array($now_group_id,$this->user_groups))
			{
				$this->user_groups[]=$now_group_id;
					
			}
		}

		//Setting the User Role
		$userRole = fetchUserRole($userid);
		//Retreiving from the vtiger_user2role
		$query="select * from ".$table_prefix."_group2role where roleid=?";
                $result = $adb->pquery($query, array($userRole));
                $num_rows=$adb->num_rows($result);
                for($i=0;$i<$num_rows;$i++)
                {
                        $now_group_id=$adb->query_result($result,$i,'groupid');
			if(! in_array($now_group_id,$this->user_groups))
			{
				$this->user_groups[]=$now_group_id;
					
			}
                }

		//Retreiving from the user2rs
		$parentRoles=getParentRole($userRole);
		$parentRolelist= array();
		foreach($parentRoles as $par_rol_id)
		{
			array_push($parentRolelist, $par_rol_id);		
		}
		array_push($parentRolelist, $userRole);
		$query="select * from ".$table_prefix."_group2rs where roleandsubid in (". generateQuestionMarks($parentRolelist) .")";
        $result = $adb->pquery($query, array($parentRolelist));
        $num_rows=$adb->num_rows($result);
        for($i=0;$i<$num_rows;$i++)
        {
            $now_group_id=$adb->query_result($result,$i,'groupid');
 
			if(! in_array($now_group_id,$this->user_groups))
			{
				$this->user_groups[]=$now_group_id;					
			}
        }
		foreach($this->user_groups as $grp_id)
		{
			$focus = new GetParentGroups();
			$focus->getAllParentGroups($grp_id);
			
			foreach($focus->parent_groups as $par_grp_id)
			{
				if(! in_array($par_grp_id,$this->user_groups))
				{
					$this->user_groups[]=$par_grp_id;
					
				}	
			}
								
		} 
		$log->debug("Exiting getAllUserGroups method...");	
	
	}
}
?>