<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("paybox.pay");
$APPLICATION->SetTitle(GetMessage("PAYMENT_SUCCESS_TITLE"));

$arrRequest = PayBoxIO::getRequest();
$strScriptName = PayBoxSignature::getOurScriptName();

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

$pgOrderId = isset( $_REQUEST["pg_order_id"] ) ? $_REQUEST["pg_order_id"] : 0;

if ($arrShopParams['ORDER_ID_TYPE']['VALUE'] === 'ORDER_NUMBER') {
    $nOrderId = \Bitrix\Sale\Order::loadByAccountNumber($pgOrderId)->getId();
} else {
    $nOrderId = intval($pgOrderId);
}

$bPay = isset($_GET['pay'])?$_GET['pay']:'n';
COption::SetOptionString("paybox.pay","pay",$bPay);
unset($_GET['pay']);


/*
 * Signature check
 */

if(!PayBoxSignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, $strSecretKey))
    print("<div class\"alert alert-danger\">Signature is not valid.</div>");
else
    if ($nOrderId != 0){
        print("<div class\"alert alert-success\">" . GetMessage("PAYMENT_SUCCESS_MESSAGE") . "</div>"); 
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
