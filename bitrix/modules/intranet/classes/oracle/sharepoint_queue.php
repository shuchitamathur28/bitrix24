<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/intranet/classes/general/sharepoint_queue.php"); 

class CIntranetSharepointQueue extends CAllIntranetSharepointQueue
{
	protected static function _LimitQuery($strWhere, $cnt)
	{
		if (!$strWhere) $strWhere = 'WHERE';
		else $strWhere .= ' AND';
		
		$strWhere .= ' ROWNUM BETWEEN 1 AND '.$cnt;
		
		return '
SELECT *
FROM b_intranet_sharepoint_queue ISPQ 
LEFT JOIN b_intranet_sharepoint ISP ON ISP.IBLOCK_ID=ISPQ.IBLOCK_ID
'.$strWhere.'
ORDER BY ID ASC';
	}
}
?>