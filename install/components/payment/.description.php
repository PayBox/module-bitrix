<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

CModule::IncludeModule("sale");
$getList = CSaleStatus::GetList(array(), array("LID" => LANGUAGE_ID));
$arrStatusName = array();
while($arrStatus = $getList->Fetch()) {
	$arrStatusName[] = $arrStatus;
}

$arrStatusIdAndName = array();
foreach($arrStatusName as $key => $value){
	$k = $arrStatusName[$key]['ID'];
	$arrStatusIdAndName[$k] = array(
		'NAME' => $arrStatusName[$key]['NAME']
	);
}

$arPSCorrespondence = array(
	'MERCHANT_ID'  => array(
		'NAME' => GetMessage("SHOP_MERCHANT_ID"),
		"DESCR" => GetMessage("SHOP_MERCHANT_ID_DESCR"),
		'SORT' => 200,
		"VALUE" => "",
		"TYPE" => "",
		'GROUP' => 'GENERAL_SETTINGS',
	),
	'SECRET_KEY'  => array(
		'NAME' => GetMessage("SHOP_SECRET_KEY"),
		"DESCR" => GetMessage("SHOP_SECRET_KEY_DESCR"),
		'SORT' => 300,
		"VALUE" => "",
		"TYPE" => "",
		'GROUP' => 'GENERAL_SETTINGS',
	),
	'STATUS_PAID'  => array(
		'NAME' => GetMessage("STATUS_PAID"),
		"DESCR" => GetMessage("STATUS_PAID_DESCR"),
		'SORT' => 400,
		"VALUE" => $arrStatusIdAndName,
		"TYPE" => "SELECT",
		'GROUP' => 'GENERAL_SETTINGS',
	),
	'STATUS_FAILED'  => array(
		'NAME' => GetMessage("STATUS_FAILED"),
		"DESCR" => GetMessage("STATUS_FAILED_DESCR"),
		'SORT' => 500,
		"VALUE" => $arrStatusIdAndName,
		"TYPE" => "SELECT",
		'GROUP' => 'GENERAL_SETTINGS',
	),
	'STATUS_REVOKED'  => array(
		'NAME' => GetMessage("STATUS_REVOKED"),
		"DESCR" => GetMessage("STATUS_REVOKED_DESCR"),
		'SORT' => 600,
		"VALUE" => $arrStatusIdAndName,
		"TYPE" => "SELECT",
		'GROUP' => 'GENERAL_SETTINGS',
	),
	'TESTING_MODE' => array(
		"NAME" => GetMessage("SHOP_TESTING_MODE"),
		"DESCR" => GetMessage("SHOP_TESTING_MODE_DESCR"),
		'SORT' => 700,
		"VALUE" => "",
		"TYPE" => "",
		'GROUP' => 'GENERAL_SETTINGS',
	),
	'TAX_TYPE'  => array(
		'NAME' => GetMessage("SHOP_TAX_TYPE"),
		"DESCR" => GetMessage("SHOP_TAX_TYPE_DESCR"),
		'SORT' => 800,
		"VALUE" => "",
		"TYPE" => "",
		'GROUP' => 'GENERAL_SETTINGS',
	),
)
?>
