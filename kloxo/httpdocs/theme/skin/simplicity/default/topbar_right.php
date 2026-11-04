<?php
$status_title = $this->print_message('simplicity');

if ($status_title !== '') {
	$status_title = str_replace(":  ", ":\n<ul style=\"margin-left:-20px\"><li>", $status_title);
	$status_title .= "</li></ul>";
}
?>

<?php if ($status_title != ''): ?>
	<div id="div_cfstatus" class="div_header_message_status">
		<?=$status_title;?>
	</div>
<?php endif; ?>

<div class="div_fixed_right modern-nav-right">
	<div class="system-status-pill" title="System Operational">
		<span class="pulse-dot"></span>
		<span>Operational</span>
	</div>

	<div id="clock_div" class="clock-pill">
<?php
	$clock_path = str_replace(getcwd(), "", getLinkCustomfile("{$skin_dir}/js", "clock.js"));
	$this->print_jscript_source($clock_path);
?>
	<script>
		function cf_fade() {
			var elemid = document.getElementById("div_cfstatus");
			if (!elemid) return;
			var op = 1;
			var timer = setInterval(function () {
				if (op <= 0.1) {
					clearInterval(timer);
					elemid.style.display = 'none';
				}
				elemid.style.opacity = op;
				op -= op * 0.1;
			}, 150);
		}

		function displayStatus(id, state) {
			var elemid = document.getElementById(id);
			if (elemid) elemid.style.display = state;
		}

		cf = setTimeout(function(){cf_fade()},15000);
		startTime('clock_div');
	</script>
	</div>

	<div class="user-profile-badge">
		<span class="user-avatar-circle">A</span>
		<span class="user-name-label"><?=$login->nname;?></span>
	</div>

	<a href="/lib/php/logout.php" class="nav-logout-btn" title="Sign out">
		<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
			<polyline points="16 17 21 12 16 7"></polyline>
			<line x1="21" y1="12" x2="9" y2="12"></line>
		</svg>
		<span>Logout</span>
	</a>
</div>
