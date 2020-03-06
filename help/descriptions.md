# Описание компонента

Компонент подготавливает фильтр для выборки из инфоблока и выводит форму фильтра для фильтрации элементов. Компонент должен подключаться перед компонентом вывода элементов каталога, иначе список элементов фильтроваться не будет. Компонент содержит четыре шаблона: .default, horizontal, bitronic-vertical, bitronic-horizontal.

В визуальном редакторе компонент расположен по пути Контент > Каталог > Kombox: Умный фильтр.

Иконка компонента имеет вид: ![](img_md/075370a5c5bf2e78e2e23d18e123cf7a.gif)

**Пример вызова**
```php
<?$APPLICATION->IncludeComponent(
      "collected:filter", 
      "horizontal", 
      array(
          "IBLOCK_TYPE" => "catalog",
          "IBLOCK_ID" => "3",
          "FILTER_NAME" => "f",
          "SECTION_ID" => "19",
          "SECTION_CODE" => "",
          "HIDE_NOT_AVAILABLE" => "N",
          "CACHE_TYPE" => "A",
          "CACHE_TIME" => "36000000",
          "CACHE_GROUPS" => "Y",
          "SAVE_IN_SESSION" => "N",
          "PAGER_PARAMS_NAME" => "arrPager",
          "INCLUDE_JQUERY" => "N",
          "PAGE_URL" => "/tv/",
          "MESSAGE_ALIGN" => "LEFT",
          "MESSAGE_TIME" => "0",
          "IS_SEF" => "N",
          "CLOSED_PROPERTY_CODE" => array(
              0 => "HDD",
              1 => "MEMORY",
              2 => "PROCESSOR",
              3 => "PROCESSOR_FREQUANCE",
              4 => "BLUETOOTH",
              5 => "LED",
              6 => "WIFI",
              7 => "SMART_TV",
              8 => "USB",
              9 => "SUPPORT_3D",
              10 => "WEIGHT",
              11 => "TIME2",
              12 => "SUPPORT_3D",
              13 => "",
          ),
          "CLOSED_OFFERS_PROPERTY_CODE" => array(
              0 => "",
              1 => "",
          ),
          "SORT" => "N",
          "FIELDS" => array(
              0 => "SECTIONS",
              1 => "STORES",
              2 => "QUANTITY",
              3 => "AVAILABLE",
          ),
          "TOP_DEPTH_LEVEL" => "0",
          "PRICE_CODE" => array(
              0 => "BASE",
          ),
          "CONVERT_CURRENCY" => "Y",
          "CURRENCY_ID" => "RUB",
          "XML_EXPORT" => "Y",
          "SECTION_TITLE" => "NAME",
          "SECTION_DESCRIPTION" => "DESCRIPTION",
          "COLUMNS" => "4",
          "STORES_ID" => array(
          )
      ),
      false
  );
?>
```
**Пример подключения в шаблоне компонента bitrix:catalog**

```php
<?$APPLICATION->IncludeComponent(
      "collected:filter", 
      "", //шаблон - .default (можно указать horizontal, bitronic_vertical или bitronic_horizontal)
      array(
          "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
          "IBLOCK_ID" => $arParams["IBLOCK_ID"],
          "FILTER_NAME" => $arParams["FILTER_NAME"],
          "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
          "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
          "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
          "CACHE_TYPE" => $arParams["CACHE_TYPE"],
          "CACHE_TIME" => $arParams["CACHE_TIME"],
          "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
          "SAVE_IN_SESSION" => "N",
          "INCLUDE_JQUERY" => "Y",
          "MESSAGE_ALIGN" => "LEFT",
          "MESSAGE_TIME" => "0",
          "IS_SEF" => "N",
          "CLOSED_PROPERTY_CODE" => array(),
          "CLOSED_OFFERS_PROPERTY_CODE" => array(),
          "SORT" => "N",
          "FIELDS" => array(),
          "PRICE_CODE" => $arParams["PRICE_CODE"],
          "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
          "CURRENCY_ID" => $arParams["CURRENCY_ID"],
          "XML_EXPORT" => "Y",
          "SECTION_TITLE" => "NAME",
          "SECTION_DESCRIPTION" => "DESCRIPTION",
          "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"]
      ),
      false
  );
?>
```
**Описание параметров**
<table class="tnormal">
<tbody>
<tr>
	<td>
 <b>Поле</b>
	</td>
	<td>
 <b>Параметр</b>
	</td>
	<td>
 <b>Описание</b>
	</td>
</tr>
<tr>
	<th colspan="3" style="text-align: left;">
 <b>Источник данных</b>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Тип инфоблока
		</p>
	</td>
	<td>
		<p align="center">
 <b>IBLOCK_TYPE</b>
		</p>
	</td>
	<td>
		<p>
			 Указывается один из созданных в системе типов информационных блоков.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Инфоблок
		</p>
	</td>
	<td>
		<p align="center">
 <b>IBLOCK_ID</b>
		</p>
	</td>
	<td>
		<p>
			 Для выбранного типа инфоблоков указывается идентификатор информационного блока, элементы которого будут отфильтрованы.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Имя выходящего массива для фильтрации
		</p>
	</td>
	<td>
		<p align="center">
 <b>FILTER_NAME</b>
		</p>
	</td>
	<td>
		<p>
			 Задается имя переменной, в которую передается массив параметров из фильтра. Если имя массива не указано, то будет использоваться значение по умолчанию.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 ID раздела
		</p>
	</td>
	<td>
		<p align="center">
 <b>SECTION_ID</b>
		</p>
	</td>
	<td>
		<p>
			 Указывается ID раздела инфоблока.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Код раздела
		</p>
	</td>
	<td>
		<p align="center">
 <b>SECTION_CODE</b>
		</p>
	</td>
	<td>
		<p>
			 Указывается символьный код раздела, из которого будут выбраны элементы. Поле может быть оставлено пустым, если указан <b>ID раздела</b>.
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Настройки кеширования</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Тип кеширования
		</p>
	</td>
	<td>
		<p align="center">
 <b>CACHE_TYPE</b>
		</p>
	</td>
	<td>
		<p>
			 Тип кеширования:
		</p>
		<p>
			 · <b>A</b> - Авто + Управляемое: автоматически обновляет кеш компонентов в течение заданного времени или при изменении данных;
		</p>
		<p>
			 · <b>Y</b> - Кешировать: для кеширования необходимо определить время кеширования;
		</p>
		<p>
			 · <b>N</b> - Не кешировать: кеширования нет в любом случае.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Время кеширования (сек.)
		</p>
	</td>
	<td>
		<p align="center">
 <b>CACHE_TIME</b>
		</p>
	</td>
	<td>
		<p>
			 Время кеширования, указанное в секундах.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Учитывать права доступа
		</p>
	</td>
	<td>
		<p align="center">
 <b>CACHE_GROUPS</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] При отмеченной опции будут учитываться права доступа при кешировании.
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Дополнительные настройки</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Сохранять установки фильтра в сессии пользователя
		</p>
	</td>
	<td>
		<p align="center">
 <b>SAVE_IN_SESSION</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] При отмеченной опции установки фильтра будут сохраняться в сессии пользователя.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Имя массива с переменными для построения ссылок в постраничной навигации
		</p>
	</td>
	<td>
		<p align="center">
 <b>PAGER_PARAMS_NAME</b>
		</p>
	</td>
	<td>
		<p>
			 Задается имя переменной, в которой передается массив с переменными для построения ссылок компонентом постраничной навигации.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Подключить библиотеку jQuery
		</p>
	</td>
	<td>
		<p align="center">
 <b>INCLUDE_JQUERY</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] При отмеченной опции компонент будет подключать библиотеку jquery.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Путь к разделу (если фильтр располагается на другой странице)
		</p>
	</td>
	<td>
		<p align="center">
 <b>PAGE_URL</b>
		</p>
	</td>
	<td>
		<p>
			 Путь к странице, на которую фильтр будет переходить при фильтрации (нужен если фильтр находится на одной странице, а результат фильтрации необходимо отобразить на другой)
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Выводить сообщение с количеством найденных элементов
		</p>
	</td>
	<td>
		<p align="center">
 <b>MESSAGE_ALIGN</b>
		</p>
	</td>
	<td>
		<p>
			 Задается с какой стороны выводить сообщение с количеством найденных элементов:
		</p>
		<p>
			 · <b>LEFT</b> - слева;
		</p>
		<p>
			 · <b>RIGHT</b> - справа;
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Через сколько секунд скрывать сообщение (0 - не скрывать)
		</p>
	</td>
	<td>
		<p align="center">
 <b>MESSAGE_TIME</b>
		</p>
	</td>
	<td>
		<p>
			 Задается интервал в секундах в течении которого будет показываться сообщение с количеством найденных элементов (0 – сообщение не будет скрываться)
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Режим самостоятельного определения текущего раздела</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Включить
		</p>
	</td>
	<td>
		<p align="center">
 <b>IS_SEF</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] Включает режим, в котором компонент сам будет определять ID текущего раздела, исходя из заданных шаблонов URL раздела и элемента. В этом режиме компоненту не нужно задавать параметры "ID раздела инфоблока" (SECTION_ID) и "Символьный код раздела инфоблока" (SECTION_CODE). В основном этот режим используется, если вы хотите отображать фильтр на всех страницах сайта, а не только в каталоге. Не путайте его с ЧПУ URL фильтра, который включается в настройках модуля.
		</p>
		<p>
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Путь к каталогу ЧПУ (относительно корня сайта)
		</p>
	</td>
	<td>
		<p align="center">
 <b>SEF_BASE_URL</b>
		</p>
	</td>
	<td>
		<p>
			 Путь к разделу, в котором расположен компонент bitrix:catalog
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Шаблон URL, ведущий на страницу с содержимым раздела
		</p>
	</td>
	<td>
		<p align="center">
 <b>SECTION_PAGE_URL</b>
		</p>
	</td>
	<td>
		<p>
			 Шаблон URL к разделу, совпадающий с параметром <b>SEF_URL_TEMPLATES[section] </b>компонента bitrix:catalog
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Шаблон URL, ведущий на страницу с содержимым элемента раздела
		</p>
	</td>
	<td>
		<p align="center">
 <b>DETAIL_PAGE_URL</b>
		</p>
	</td>
	<td>
		<p>
			 Шаблон URL к детальной странице товара, совпадающий с параметром <b>SEF_URL_TEMPLATES[element] </b>компонента bitrix:catalog
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Настройки полей</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Свойства, которые будут свернуты
		</p>
	</td>
	<td>
		<p align="center">
 <b>CLOSED_PROPERTY_CODE</b>
		</p>
	</td>
	<td>
		<p>
			 Указываются свойства инфоблока, которые будут по умолчанию свернуты при показе фильтра. При выборе пункта <i>(не выбрано)-&gt;</i> и без указания кодов свойств в строках ниже, свойства свернуты не будут.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Свойства предложений, которые будут свернуты
		</p>
	</td>
	<td>
		<p align="center">
 <b>CLOSED_OFFERS_PROPERTY_CODE</b>
		</p>
	</td>
	<td>
		<p>
			 Указываются свойства предложений, которые будут по умолчанию свернуты при показе фильтра. При выборе пункта <i>(не выбрано)-&gt;</i> и без указания кодов свойств в строках ниже, свойства свернуты не будут.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Сортировать поля фильтра
		</p>
	</td>
	<td>
		<p align="center">
 <b>SORT</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] Включает сортировку полей фильтра по индексу сортировки
		</p>
		<p>
			 Поля фильтра будут отсортированы по индексу сортировки. Для свойств индексы сортировки можно задать в форме настроек инфоблока, для цен - в настройках цены, для дополнительных полей (Склады, Подразделы и т.д.) в настроках компонента (см. ниже).
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Порядок сортировки полей
		</p>
	</td>
	<td>
		<p align="center">
 <b>SORT_ORDER</b>
		</p>
	</td>
	<td>
		<p>
			 Указывает направление сортировки полей:
		</p>
		<p>
			 · <b>ASC</b> – по возрастанию;
		</p>
		<p>
			 · <b>DESC</b> – по убыванию;
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Показывать поля в фильтре
		</p>
	</td>
	<td>
		<p align="center">
 <b>FIELDS</b>
		</p>
	</td>
	<td>
		<p>
			 Указываются поля, которые будут выведены в фильтре. Массив, который может содержать значения:
		</p>
		<p>
			 · <b>SECTIONS</b> - подразделы;
		</p>
		<p>
			 · <b>STORES</b> - склады;
		</p>
		<p>
			 · <b>QUANTITY </b>– количество на складе
		</p>
		<p>
			 · <b>AVAILABLE </b>– наличие на складе
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Индекс сортировки поля "Подразделы"
		</p>
	</td>
	<td>
		<p align="center">
 <b>SORT_SECTIONS</b>
		</p>
	</td>
	<td>
		<p>
			 Индекс сортировки для поля «Подразделы» - число
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Максимальная отображаемая глубина разделов
		</p>
	</td>
	<td>
		<p align="center">
 <b>TOP_DEPTH_LEVEL</b>
		</p>
	</td>
	<td>
		<p>
			 Число – указывает максимальную глубину вложенности подразделов для поля «Подразделы», относительно текущего раздела (0 – неограниченная глубина).
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Индекс сортировки поля "Склады"
		</p>
	</td>
	<td>
		<p align="center">
 <b>SORT_STORES</b>
		</p>
	</td>
	<td>
		<p>
			 Индекс сортировки для поля «Склады» - число
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Индекс сортировки поля "Количество"
		</p>
	</td>
	<td>
		<p align="center">
 <b>SORT_QUANTITY</b>
		</p>
	</td>
	<td>
		<p>
			 Индекс сортировки для поля «Количество» - число
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Индекс сортировки поля "Наличие"
		</p>
	</td>
	<td>
		<p align="center">
 <b>SORT_AVAILABLE</b>
		</p>
	</td>
	<td>
		<p>
			 Индекс сортировки для поля «Наличие» - число
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Цены</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Тип цены
		</p>
	</td>
	<td>
		<p align="center">
 <b>PRICE_CODE</b>
		</p>
	</td>
	<td>
		<p>
			 Указывается тип цены для выводимых элементов.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Показывать цены в одной валюте
		</p>
	</td>
	<td>
		<p align="center">
 <b>CONVERT_CURRENCY</b>
		</p>
	</td>
	<td>
		<p>
			 При установке флажка цены будут выводиться в одной валюте, даже если в каталоге они будут заданы в разных валютах. При выборе этой опции кеш компонента будет автоматически сбрасываться при изменении курсов валют тех товаров, что показываются компонентом. К примеру, если выбрана конвертация в рубли, а цены в инфоблоке сохранены в евро, то кеш сбросится при изменении курса евро или рубля. Изменения остальных валют на кеш не окажут влияния.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Валюта, в которую будут сконвертированы цены
		</p>
	</td>
	<td>
		<p align="center">
 <b>CURRENCY_ID</b>
		</p>
	</td>
	<td>
		<p>
			 Выбор валюты в которой будут отображаться цены.
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Складской учет</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Не отображать товары, которых нет на складах
		</p>
	</td>
	<td>
		<p align="center">
 <b>HIDE_NOT_AVAILABLE</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] При отмеченной опции будут скрыты товары, для которых общее количество на складах меньше либо равно нулю, включен количественный учет и не разрешена покупка при отсутствии товара.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Выводить товары со складов
		</p>
	</td>
	<td>
		<p align="center">
 <b>STORES_ID</b>
		</p>
	</td>
	<td>
		<p>
			 Указывает те склады, товары с которых будут отображаться и по которым будет осуществляться фильтрация. Чтобы в фильтрации участвовали все склады, оставьте список пустым.
		</p>
		<p>
 <i>Рекомендуемое значение - все склады.</i>
		</p>
		<p>
 <b>Этот параметр может увеличить нагрузку на сервер</b>
		</p>
	</td>
</tr>
<tr>
	<th colspan="3">
		<p style="text-align: left;">
 <b>Поддержка Яндекс Островов (экспорт фильтра в XML)</b>
		</p>
	</th>
</tr>
<tr>
	<td>
		<p>
			 Включить поддержку Яндекс Островов
		</p>
	</td>
	<td>
		<p align="center">
 <b>XML_EXPORT</b>
		</p>
	</td>
	<td>
		<p>
			 [Y|N] При отмеченной опции будет включена поддержка Яндекс Островов.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Заголовок
		</p>
	</td>
	<td>
		<p align="center">
 <b>SECTION_TITLE</b>
		</p>
	</td>
	<td>
		<p>
			 Указывается поле, которое будет использоваться в качестве заголовка раздела.
		</p>
	</td>
</tr>
<tr>
	<td>
		<p>
			 Описание
		</p>
	</td>
	<td>
		<p align="center">
 <b>SECTION_DESCRIPTION</b>
		</p>
	</td>
	<td>
		<p>
			 Задается поле, которое будет использоваться в качестве описания раздела.
		</p>
	</td>
</tr>
</tbody>
</table>