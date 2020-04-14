<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("paybox.pay");
$APPLICATION->SetTitle("????? ? ??????");

$strScriptName = PayBoxSignature::getOurScriptName();
$arrRequest = PayBoxIO::getRequest();

$objShop = CSalePaySystemAction::GetList('', array("PAY_SYSTEM_ID"=>$arrRequest['PAYMENT_SYSTEM']));
$arrShop = $objShop->Fetch();
if(!empty($arrShop))
	$arrShopParams = unserialize($arrShop['PARAMS']);
else
{
	PayBoxIO::makeResponse($strScriptName, '', 'error',
		'Please re-configure the module PayBox.PAY in Bitrix CMS. The payment system should have a name '.$arrRequest['PAYMENT_SYSTEM']);
}

$strSecretKey = $arrShopParams['SECRET_KEY']['VALUE'];

$nOrderId = intval(isset( $_REQUEST["pg_order_id"] ) ? $_REQUEST["pg_order_id"] : 0 );

$bPay = isset($_GET['pay'])?$_GET['pay']:'n';
COption::SetOptionString("paybox.pay","pay",$bPay);
unset($_GET['pay']);

/*
 * Signature check
 */

if(!PayBoxSignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, $strSecretKey) )
    print("Signature is not valid.");
else
	if ($nOrderId != 0){
		print("????? ? ??????");
		print(" <a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&pay=y'>?????????? ???????? ??? ???.</a>");
  
		$APPLICATION->IncludeComponent(
			"bitrix:sale.personal.order.detail",
			"",
			Array(
				"PATH_TO_LIST" => "", // path to list
				"PATH_TO_CANCEL" => "", // path to cancel
				"PATH_TO_PAYMENT" => "payment.php", // path to payment
				"ID" => $nOrderId,
				"SET_TITLE" => "Y"
				)
			);

	}
	else
		die("Invalid params.");
  
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
