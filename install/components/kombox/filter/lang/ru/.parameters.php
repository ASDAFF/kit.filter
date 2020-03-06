<?
/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

$MESS["KOMBOX_CMP_FILTER_GROUP_SEF"] = "Режим самостоятельного определения текущего раздела";
$MESS["KOMBOX_CMP_FILTER_GROUP_FIELDS"] = "Настройки полей";
$MESS["KOMBOX_CMP_FILTER_GROUP_STORES"] = "Складской учет";
$MESS["KOMBOX_CMP_FILTER_GROUP_PRICES"] = "Цены";
$MESS["KOMBOX_CMP_FILTER_IBLOCK_TYPE"] = "Тип инфоблока";
$MESS["KOMBOX_CMP_FILTER_IBLOCK_ID"] = "Инфоблок";
$MESS["KOMBOX_CMP_FILTER_SECTION_ID"] = "ID раздела инфоблока";
$MESS["KOMBOX_CMP_FILTER_SECTION_CODE"] = "Символьный код раздела инфоблока";
$MESS["KOMBOX_CMP_FILTER_PRICE_CODE"] = "Тип цены";
$MESS["KOMBOX_CMP_FILTER_FILTER_NAME"] = "Имя выходящего массива для фильтрации";
$MESS["KOMBOX_CMP_FILTER_SAVE_IN_SESSION"] = "Сохранять установки фильтра в сессии пользователя";
$MESS["KOMBOX_CMP_FILTER_CACHE_GROUPS"] = "Учитывать права доступа";
$MESS["KOMBOX_CMP_FILTER_INSTANT_RELOAD"] = "Мгновенная фильтрация при включенном AJAX";
$MESS["KOMBOX_CMP_FILTER_CLOSED_PROPERTY_CODE"] = "Свойства, которые будут свернуты";
$MESS["KOMBOX_CMP_FILTER_CLOSED_OFFERS_PROPERTY_CODE"] = "Свойства предложений, которые будут свернуты";
$MESS["KOMBOX_CMP_FILTER_INCLUDE_JQUERY"] = "Подключить библиотеку jQuery";
$MESS["KOMBOX_CMP_FILTER_GROUP_XML_EXPORT"] = "Поддержка Яндекс Островов (экспорт фильтра в XML)";
$MESS["KOMBOX_CMP_FILTER_XML_EXPORT"] = "Включить поддержку Яндекс Островов";
$MESS["KOMBOX_CMP_FILTER_SECTION_TITLE"] = "Заголовок";
$MESS["KOMBOX_CMP_FILTER_SECTION_DESCRIPTION"] = "Описание";
$MESS["KOMBOX_CMP_FILTER_IS_SEF"] = "Включить";
$MESS["KOMBOX_CMP_FILTER_SEF_BASE_URL"] = "Путь к каталогу (относительно корня сайта)";
$MESS["KOMBOX_CMP_FILTER_SECTION_PAGE_URL"] = "Шаблон URL, ведущий на страницу с содержимым раздела";
$MESS["KOMBOX_CMP_FILTER_DETAIL_PAGE_URL"] = "Шаблон URL, ведущий на страницу с содержимым элемента раздела";
$MESS["KOMBOX_CMP_FILTER_PAGE_URL"] = "Путь к разделу (если фильтр располагается на другой странице)";
$MESS["KOMBOX_CMP_FILTER_MESSAGE_ALIGN"] = "Выводить сообщение с количеством найденных элементов";
$MESS["KOMBOX_CMP_FILTER_MESSAGE_ALIGN_LEFT"] = "Слева";
$MESS["KOMBOX_CMP_FILTER_MESSAGE_ALIGN_RIGHT"] = "Справа";
$MESS["KOMBOX_CMP_FILTER_MESSAGE_TIME"] = "Через сколько секунд скрывать сообщение (0 - не скрывать)";
$MESS["KOMBOX_CMP_FILTER_CONVERT_CURRENCY"] = "Показывать цены в одной валюте";
$MESS["KOMBOX_CMP_FILTER_CURRENCY_ID"] = "Валюта, в которую будут сконвертированы цены";
$MESS["KOMBOX_CMP_FILTER_FIELDS"] = "Показывать дополнительные поля в фильтре";
$MESS["KOMBOX_CMP_FILTER_FIELDS_STORES"] = "Склады";
$MESS["KOMBOX_CMP_FILTER_FIELDS_SECTIONS"] = "Подразделы";
$MESS["KOMBOX_CMP_FILTER_FIELDS_AVAILABLE"] = "Наличие";
$MESS["KOMBOX_CMP_FILTER_FIELDS_QUANTITY"] = "Количество";
$MESS["KOMBOX_CMP_FILTER_HIDE_NOT_AVAILABLE"] = "Не отображать товары, которых нет на складах";

$MESS["KOMBOX_CMP_FILTER_SORT"] = "Сортировать поля фильтра";
$MESS["KOMBOX_CMP_FILTER_SORT_ORDER"] = "Порядок сортировки полей";
$MESS["KOMBOX_CMP_FILTER_SORT_ORDER_ASC"] = "по возрастанию";
$MESS["KOMBOX_CMP_FILTER_SORT_ORDER_DESC"] = "по убыванию";
$MESS["KOMBOX_CMP_FILTER_SORT_STORES"] = "Индекс сортировки поля \"Склады\"";
$MESS["KOMBOX_CMP_FILTER_SORT_SECTIONS"] = "Индекс сортировки поля \"Подразделы\"";
$MESS["KOMBOX_CMP_FILTER_SORT_AVAILABLE"] = "Индекс сортировки поля \"Наличие\"";
$MESS["KOMBOX_CMP_FILTER_SORT_QUANTITY"] = "Индекс сортировки поля \"Количество\"";
$MESS["KOMBOX_CMP_FILTER_TOP_DEPTH_LEVEL"] = "Максимальная отображаемая глубина разделов (0 - все разделы)";
$MESS["KOMBOX_CMP_FILTER_STORES_ID"] = "Выводить товары со складов (по умолчанию - все склады)";

$MESS["IS_SEF_TIP"] = "
Включает режим, в котором компонент сам будет определять ID текущего раздела, исходя из заданных шаблонов URL раздела и элемента. В этом режиме компоненту не нужно задавать параметры \"ID раздела инфоблока\" (SECTION_ID) и 
\"Символьный код раздела инфоблока\" (SECTION_CODE). В основном этот режим используется, если вы хотите отображать фильтр на всех страницах сайта, а не только в каталоге. Не путайте его с ЧПУ URL фильтра, который включается в 
<a href=\"/bitrix/admin/settings.php?mid=kombox.filter\">настройках модуля</a>.";

$MESS["SORT_TIP"] = "Поля фильтра будут отсортированы по индексу сортировки. Для свойств индексы сортировки можно задать в форме настроек инфоблока, для цен - в настройках цены, для дополнительных полей (Склады, Подразделы) в настроках компонента (см. ниже).";

$MESS["STORES_ID_TIP"] = "Рекомендуемое значение - все склады.<br /> Выберете те склады, товары с которых будут отображаться и по которым будет осуществляться фильтрация. Чтобы в фильтрации участвовали все склады, оставьте список пустым.<br /><b>Этот параметр может увеличить нагрузку на сервер</b>";
?>