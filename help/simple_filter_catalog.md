# Каталог на простых компонентах

В этом случае фильтр размещаем аналогично тому как мы это делаем на [отдельной странице сайта](simple_page.md), т.е. открываем страницу с товарами в визуальном редакторе и размещаем на ней компонент Kombox:Умный фильтр с ЧПУ. Опять же не забываем что компонент фильтра должен располагаться ДО вызова компонента bitrix:catalog.section. Приведу пример вызова компонента фильтра на такой странице:

```php
<?
IncludeComponent(
	"kit:filter", 
	".default", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "2",
		"FILTER_NAME" => "arrFilter",
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_CODE" => $_REQUEST["SECTION_CODE"],
		"HIDE_NOT_AVAILABLE" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"SAVE_IN_SESSION" => "N",
		"INCLUDE_JQUERY" => "Y",
		"PAGE_URL" => "",
		"MESSAGE_ALIGN" => "LEFT",
		"MESSAGE_TIME" => "5",
		"IS_SEF" => "N",
		"CLOSED_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"CLOSED_OFFERS_PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"SORT" => "N",
		"FIELDS" => array(
		),
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"CONVERT_CURRENCY" => "N",
		"XML_EXPORT" => "N",
		"THEME" => "ice",
		"SECTION_TITLE" => "-",
		"SECTION_DESCRIPTION" => "-",
		"STORES_ID" => array(
		),
        "PAGER_PARAMS_NAME" => "arrPager"
	),
	false
);
?>
```

Как настроить ЧПУ для этого случая можно прочитать здесь.