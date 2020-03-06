<?if(!check_bitrix_sessid()) return;?>
<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if (!check_bitrix_sessid())
	return;

IncludeModuleLangFile(__FILE__);
if ($ex = $APPLICATION->GetException())
	echo CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else 
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<form action="<?echo $APPLICATION->GetCurPage()?>" method="get">
<p>
	<input type="hidden" name="lang" value="<?echo LANG?>" />
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>" />
</p>
<form>