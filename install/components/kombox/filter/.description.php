<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("KOMBOX_CMP_FILTER_NAME"),
	"DESCRIPTION" => GetMessage("KOMBOX_CMP_FILTER_DESCRIPTION"),
	"ICON" => "/images/kombox_filter.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 70,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("KOMBOX_CMP_FILTER_CATALOG"),
			"SORT" => 30,
		),
	),
);
?>