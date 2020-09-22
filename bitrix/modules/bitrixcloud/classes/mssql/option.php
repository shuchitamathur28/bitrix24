<?
class CBitrixCloudOption extends CAllBitrixCloudOption
{
	/**
	 *
	 * @param string $name
	 * @return void
	 *
	 */
	public function __construct($name)
	{
		parent::__construct($name);
	}
	/**
	 * Fabric method
	 *
	 * @param string $name
	 * @return CBitrixCloudOption
	 *
	 */
	public static function getOption($name)
	{
		$ob = new CBitrixCloudOption($name);
		return $ob;
	}
	/**
	 * @return bool
	 *
	 */
	public static function lock()
	{
		global $DB;
		$DB->StartTransaction();
		$DB->Query("SET LOCK_TIMEOUT 0", true);
		$db_lock = $DB->Query("SELECT * FROM b_bitrixcloud_option WITH (TABLOCKX)", true);
		if (is_object($db_lock))
		{
			$DB->Query("SET LOCK_TIMEOUT -1", true);
			return true;
		}
		else
		{
			$DB->Query("SET LOCK_TIMEOUT -1", true);
			$DB->Commit();
			return false;
		}
	}
	/**
	 * @return void
	 *
	 */
	public static function unlock()
	{
		global $DB;
		$DB->Commit();
	}
}
?>