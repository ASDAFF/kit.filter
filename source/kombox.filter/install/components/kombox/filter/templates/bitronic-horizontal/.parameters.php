<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arTemplateParameters = array(
	"THEME" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("KOMBOX_CMP_FILTER_THEME"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"red" => GetMessage("KOMBOX_CMP_FILTER_THEME_RED"),
			"ice" => GetMessage("KOMBOX_CMP_FILTER_THEME_ICE"),
			"green" => GetMessage("KOMBOX_CMP_FILTER_THEME_GREEN"),
			"pink" => GetMessage("KOMBOX_CMP_FILTER_THEME_PINK"),
			"yellow" => GetMessage("KOMBOX_CMP_FILTER_THEME_YELLOW"),			
			"metal" => GetMessage("KOMBOX_CMP_FILTER_THEME_METAL")		
		),
		"DEFAULT" => "red",
	)
);
?>