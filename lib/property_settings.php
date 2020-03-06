<?

/**
 * Copyright (c) 6/3/2020 Created By/Edited By ASDAFF asdaff.asad@yandex.ru
 */

namespace Collected\Filter;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PropertySettingsTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}
	
	public static function getTableName()
	{
		return 'b_collected_filter_prop_settings';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'PROPERTY_ID' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HINT_TYPE' => array(
				'data_type' => 'string'
			),
			'HINT' => array(
				'data_type' => 'text'
			),
			'VIEW' => array(
				'data_type' => 'string'
			),
			'SLIDER_STEP' => array(
				'data_type' => 'float'
			),
			'SLIDER_UNITS' => array(
				'data_type' => 'string'
			),
			'LIST_SIZE' => array(
				'data_type' => 'integer'
			),
			'LIST_MULTI' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'VALUES_CNT' => array(
				'data_type' => 'integer'
			),
			'SORT' => array(
				'data_type' => 'string'
			),
			'SORT_ORDER' => array(
				'data_type' => 'string'
			)
		);
	}
}
?>