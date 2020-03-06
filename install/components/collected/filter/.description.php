<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COLLECTED_CMP_FILTER_NAME"),
	"DESCRIPTION" => GetMessage("COLLECTED_CMP_FILTER_DESCRIPTION"),
	"ICON" => "/images/collected_filter.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 70,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("COLLECTED_CMP_FILTER_CATALOG"),
			"SORT" => 30,
		),
	),
);
?>