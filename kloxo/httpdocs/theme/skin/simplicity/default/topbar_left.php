<?php
$mess_url = "/display.php?frm_action=list&frm_o_cname=smessage";

$loginas = $login->nname;

$total_message_received = db_get_count("smessage", "text_sent_to_cmlist LIKE '%client-{$loginas}%'");
$total_message_readby = db_get_count("smessage", "text_readby_cmlist LIKE '%client-{$loginas}%'");
$total_message_unreadby = $total_message_received - $total_message_readby;

$message_text = "{$total_message_unreadby}/{$total_message_received}";

$total_ticket_received = db_get_count("ticket", "sent_to='client-{$loginas}'");
$total_ticket_open = db_get_count("ticket", "sent_to='client-{$loginas}' AND state='open'");

$ticket_text = "{$total_ticket_open}/{$total_ticket_received}";
?>

<div class="div_fixed_left modern-nav-left">
	<a href="/display.php?frm_action=show" class="brand-badge-link" title="Dashboard">
		<div class="brand-icon-box">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
				<polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
				<polyline points="2 17 12 22 22 17"></polyline>
				<polyline points="2 12 12 17 22 12"></polyline>
			</svg>
		</div>
		<span class="brand-name-text">Mfklixo</span>
	</a>

	<div class="nav-stat-group">
		<a href="/display.php?frm_action=list&frm_o_cname=smessage" class="nav-stat-pill" title="Unread Messages / Total">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
				<polyline points="22,6 12,13 2,6"></polyline>
			</svg>
			<span>Msg: <?=$message_text;?></span>
		</a>
		<a href="/display.php?frm_action=list&frm_o_cname=ticket" class="nav-stat-pill" title="Open Tickets / Total">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
				<polyline points="14 2 14 8 20 8"></polyline>
			</svg>
			<span>Tickets: <?=$ticket_text;?></span>
		</a>
	</div>
</div>
