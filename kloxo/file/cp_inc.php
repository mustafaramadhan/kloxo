<?php
	$ha = str_replace('cp.', '', $_SERVER["HTTP_HOST"]);
?>
		<br><br>
		<div align="center">
			<table width="400" style="border-collapse: collapse" border="1" bordercolor="#AAAAAA">
				<tr>
					<td nowrap bgcolor="#CCCCCC">&nbsp;</td>
					<td colspan="2" nowrap align="center" bgcolor="#EEEEEE">Panel</td>
				</tr>
				<tr>
					<td nowrap bgcolor="#eeeeee">&nbsp; Kloxo</td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="http://<?php echo $ha; ?>:7778/">http</a></td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="https://<?php echo $ha; ?>:7777/">https</a></td>
				</tr>
				<tr>
					<td nowrap bgcolor="#eeeeee">&nbsp; Webmail</td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="http://webmail.<?php echo $ha; ?>/">http</a></td>
					<td nowrap align="center" bgcolor="#FFFFCC"><a href="https://webmail.<?php echo $ha; ?>/">https</a></td>
				</tr>
				<tr>
					<td nowrap bgcolor="#eeeeee" width="200">&nbsp; PHPMyAdmin</td>
					<td nowrap align="center" width="100" bgcolor="#FFFFCC">
					<a href="http://<?php echo $ha; ?>:7778/thirdparty/phpMyAdmin/">
					http</a></td>
					<td nowrap align="center" width="100" bgcolor="#FFFFCC">
					<a href="https://<?php echo $ha; ?>:7777/thirdparty/phpMyAdmin/">
					https</a></td>
				</tr>
			</table>
		</div>
