<?php 

include_once "lib/html/include.php";

$ghtml = new HtmlLib();

//$res["Super Admin"] = array('superadmin', 'superclient');
$res["Admin"] = array('admin', 'client');
//$res['Wholesale Reseller'] = array('wholesale', 'client');
$res['Reseller'] = array('reseller', 'client');
$res['Customer/Domain Owner'] = array('customer', 'client');
//$res['Simple Skin'] = array('sillyskin', 'client');
$res['Mail Account'] = array('postmaster@example.com', 'mailaccount');

$color = "style='border:1px solid black'";
?>

<table cellspacing="0" cellpadding="0">
<tr> <td colspan="2">Demo:</td></tr>

<?php
foreach($res as $k => $v) {
	$formname = $v[0] . "_" . $v[1];
	$class = $v[1];
	$name = $v[0];

	$color = null;

	if ($class == 'superadmin') {
		$color = "border-bottom: 1px solid black";
	}

	$formname = str_replace(array('@', '.'), "", $formname);
?>
	<tr >
		<td style="<?= $color ?>">
			<form name="<?= $formname ?>" method="<?= $sgbl->method ?>" action="/lib/php/">
				<input type="hidden" name="frm_clientname" value="<?= $v[0] ?>">
				<input type="hidden" name="frm_class" value="<?= $v[1] ?>">
				<input type="hidden" name="frm_password" value="lxlabs">
			</form>
<?php
	
	if ($class == 'client') {
		$var = "cttype_v_$name";
	} else {
		$var = 'show';
	}

?>
		</td>
		<td style="<?= $color ?>"><a href="javascript:document.<?= $formname ?>.submit()"> Click here to Login as <?= $k ?> (<?= $v[0] ?>)</a></td>
	</tr>
<?php
}
?>

	<tr>
		<td colspan='2'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='2'>Links:</td>
	</tr>
	<tr>
		<td>&nbsp;-&nbsp;</td>
		<td><a href=http://forum.mratwork.com/ target='_blank'>Visit our forums</a></td>
	</tr>
	<tr>
		<td>&nbsp;-&nbsp;</td>
		<td><a href=http://mratwork.com/ target='_blank'>MRatWork</a></td>
	</tr>
</table>