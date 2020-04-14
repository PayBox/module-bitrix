<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

CModule::IncludeModule("sale");
CModule::IncludeModule("paybox.pay");

$useNew = GetMessage("USE_NEW");
$newEmail = GetMessage("NEW_EMAIL");
$useNewEmail = GetMessage("USE_NEW_EMAIL");

$arrRequestMethods = array("POST", "GET");
$arrUserRedirectMethods = array("POST", "GET", "AUTOPOST", "AUTOGET");

$userID = $USER->GetID();
$rsUser = CUser::GetByID($userID);
$arrUser = $rsUser->Fetch();

$strCustomerEmail = $arrUser['EMAIL'];
$strCustomerPhone = PayBoxIO::checkAndConvertUserPhone($arrUser['PERSONAL_MOBILE']);

if ($_SERVER["REQUEST_METHOD"] == "POST" && trim($_POST["SET_NEW_USER_DATA"])!="")
{
    if(!empty($_POST["NEW_EMAIL"]))
        $strCustomerEmail = $_POST["NEW_EMAIL"];
    if(!empty($_POST["NEW_PHONE"]))
        $strCustomerPhone = $_POST["NEW_PHONE"];
}

if(!PayBoxIO::emailIsValid($strCustomerEmail)){
    echo "
			<form method=\"POST\" action=\"".POST_FORM_ACTION_URI."\">
			<p><font color=\"Red\">$newEmail</font></p>
			<input type=\"text\" name=\"NEW_EMAIL\" size=\"30\" value=\"$strCustomerEmail\" />";
    echo "<br><br>
			<input type=\"submit\" name=\"SET_NEW_USER_DATA\" value=\"$useNew".
        (!PayBoxIO::emailIsValid($strCustomerEmail)? "$useNewEmail" : "").
        "\" />
	</form>";
    exit();
}

$order = \Bitrix\Sale\Order::load($_GET['ORDER_ID']);

$nAmount = $order->getPrice();
$nMerchantId = CSalePaySystemAction::GetParamValue("MERCHANT_ID");
$strSecretKey = CSalePaySystemAction::GetParamValue("SECRET_KEY");
$bTestingMode = CSalePaySystemAction::GetParamValue("TESTING_MODE") == "Y"? 1 : 0;
$nOrderId = $order->getId();
$nPSId = $order->getPaySystemIdList()[0];

$strStatusPaid = CSalePaySystemAction::GetParamValue("STATUS_PAID");
$strStatusFailed = CSalePaySystemAction::GetParamValue("STATUS_FAILED");
$strStatusRevoked = CSalePaySystemAction::GetParamValue("STATUS_REVOKED");

$nAmount = number_format($nAmount, 2, '.', '');

$arrRequest['pg_salt'] = uniqid();
$arrRequest['pg_merchant_id'] = $nMerchantId;
$arrRequest['pg_order_id']    = $nOrderId;
$arrRequest['pg_lifetime']    = 3600*24;
$arrRequest['pg_amount']      = $nAmount;
$arrRequest['pg_currency'] = $order->getCurrency();

$basketList = CSaleBasket::GetList(array(), array("ORDER_ID" => $nOrderId));
$arrItems = array();
while ($arrItem = $basketList->Fetch()) {
    $arrItems[] = $arrItem['NAME'].', ';
}

$arrRequest['pg_description'] = 'Order ID: '.$nOrderId;
$arrRequest['pg_user_phone'] = $strCustomerPhone;
$arrRequest['pg_user_contact_email'] = $strCustomerEmail;
$arrRequest['pg_user_email'] = $strCustomerEmail;
$arrRequest['pg_user_ip'] = $_SERVER['REMOTE_ADDR'];
$arrRequest['pg_sire_url']		= "http://".$_SERVER['HTTP_HOST'];
$arrRequest['pg_check_url']		= "https://".$_SERVER['HTTP_HOST']."/paybox/check.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_result_url'] = "https://".$_SERVER['HTTP_HOST']."/paybox/result.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_request_method']	= 'POST';
$arrRequest['pg_success_url']   = "https://".$_SERVER['HTTP_HOST']."/paybox/success.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_refund_url']   = "https://".$_SERVER['HTTP_HOST']."/paybox/refund.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_success_url_method']	= 'AUTOPOST';
$arrRequest['pg_failure_url']   = "https://".$_SERVER['HTTP_HOST']."/paybox/failure.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_failure_url_method']	= 'AUTOPOST';
$arrRequest['STATUS_PAID'] = $strStatusPaid;
$arrRequest['STATUS_FAILED'] = $strStatusFailed;
$arrRequest['STATUS_REVOKED'] = $strStatusRevoked;

if($bTestingMode)
    $arrRequest['pg_testing_mode']	= '1';

$arrRequest['pg_encoding'] = 'windows-1251';
$arrRequest['pg_sig'] = PayBoxSignature::make('payment.php', $arrRequest, $strSecretKey);


/*
 * PayBox Request
 */
print "<form name=\"payment\" method='".$strRequestMethod."' action='https://paybox.kz/payment.php'";
foreach($arrRequest as $key => $value) {
    print "<label for=''>				<input type='hidden' name='".$key."' value='".$value."' />			</label>";
}

print "<input type=\"submit\"></form>";
?>
<script>
    document.payment.submit();
</script>
