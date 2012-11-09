<?php

class SpecialPlay_b extends Lxaclass
{
	static $__desc_demo_status = array("ef", "", "demo_status");
	static $__desc_demo_status_off = array("", "", "demo_status");
	static $__desc_demo_status_on = array("", "", "password");
	static $__desc_skin_name = array("", "", "skin");
	static $__desc_skin_name_v_default = array("", "", "skin");
	static $__desc_skin_color = array("", "", "skin_color");
	static $__desc_skin_color_v_default = array("", "", "skin");
	static $__desc_icon_name = array("", "", "icon_name");
	static $__desc_logo_image = array("", "", "current_logo_url");
	static $__desc_login_page = array("", "", "login_to");
//	static $__desc_logo_image_loading = array("", "",  "current_logo_url_shown_while_loading");
	static $__desc_show_quickaction = array("f", "", "show_quickaction");
	static $__desc_disable_quickaction = array("f", "", "disable_quickaction");
	static $__desc_icon_name_v_collage = array("", "", "skin");
	static $__desc_show_navig = array("fe", "", "show_navigation_bar");
	static $__desc_show_navig_v_on = array("", "", "skin");
	static $__desc_ultra_navig = array("fe", "", "enable_ultra_navigation");
	static $__desc_split_frame = array("f", "", "split_frame_view");
	static $__desc_close_add_form = array("f", "", "keep_the_add_forms_closed_by_default");
	static $__desc_disableipcheck = array("f", "", "disable_ip_check");
	static $__desc_disable_ajax = array("f", "", "disable_dialog_boxes");
	static $__desc_enable_ajax = array("f", "", "enable_ajax_(Needs_high_bandwidth)");
	static $__desc_cpanel_skin = array("f", "", "simple_skin");
	static $__desc_simple_skin = array("f", "", "simple_skin_(Recommended_for_end_user)");
	static $__desc_show_thin_header = array("f", "", "show_thin_header");
	static $__desc_resource_bottom = array("fe", "", "show_resource_at_bottom");
	static $__desc_resource_bottom_v_off = array("fe", "", "show_resource_at_bottom");
	static $__desc_interface_template = array("", "", "interface_template");
	static $__desc_show_brethren_list = array("e", "", "show_list_of_brethren");
	static $__desc_show_brethren_list_v_top = array("", "", "enable_ultra_navigation");
	static $__desc_dont_show_disabled_permission = array("f", "", "dont_show_disabled_permission");
	static $__desc_show_brethren_list_v_left = array("", "", "enable_ultra_navigation");
	static $__desc_show_brethren_list_v_off = array("", "", "enable_ultra_navigation");
	static $__desc_ultra_navig_v_off = array("", "", "skin");
	static $__desc_language = array("", "", "language");
	static $__desc_language_en = array("", "", "english");
	static $__desc_show_lpanel = array("", "", "skin");
	static $__desc_disable_docroot = array("f", "", "disable_ability_to_set_docroot");
	static $__desc_show_lpanel_v_on = array("", "", "skin");
	static $__desc_customermode_flag = array("f", "", "log_into_domain_owner_mode");
	static $__desc_show_help = array("f", "", "show_xp_like_left_panel");
	static $__desc_show_help_v_on = array("", "", "");
	static $__desc_ssession_timeout = array("", "", "session_timeout_(in_secs)");
	static $__desc_show_add_buttons = array("f", "", "show_add_buttons_in_main_page");
	static $__desc_lpanel_scrollbar = array("f", "", "enable_scroll_bar_in_tree_menu");
	static $__desc_lpanel_group_resource = array("f", "", "group_resources_in_tree_menu");
	static $__desc_lpanel_group_resource_v_off = array("", "", "alphabetically_group_resources_in_tree_menu");
	static $__desc_lpanel_depth = array("n", "", "depth_of_resource_in_the_tree_menu");
	static $__desc_lpanel_scrollbar_v_off = array("", "", "skin");
	static $__desc_per_page = array("", "", "lines_per_page");
	static $__desc_per_page_v_10 = array("", "", "telephone_no");

	function isCoreLanguage()
	{
		if (!$this->language) {
			return true;
		}

		return ($this->language === 'en');
	}

	function defaultValue($var)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$progname = $sgbl->__var_program_name;

		if ($var === 'logo_image') {
			return "/img/$progname-logo.gif";
		}

		if ($var === 'logo_image_loading') {
			return "/img/$progname-splash.gif";
		}

		/*
				// temporary only for admin - 6.1.7
				if ($var === 'logo_image') {
					return "/img/user-logo.png";
				}
			 */
		if ($var === 'ssession_timeout') {
			return 18000;
		}

		if ($var === 'lpanel_depth') {
			return 3;
		}

		if ($var === 'language') {
			return 'en';
		}

		return null;
	}
}

class sp_basespecialplay extends LxspecialClass
{
	static $__desc = array("", "", "display");
	static $__desc_nname = array("", "", "account");

//	static $__desc_logo_image_f  = array("F","",  "upload_logo_image_(gif)");
//	static $__desc_logo_image_loading_f  = array("F","",  "upload_logo_image_shown_while_loading_(gif)");
	static $__desc_logo_image_f = array("F", "", "upload_logo_image_(png)");

	static $__desc_specialplay_b = array("", "", "skin");
	static $__acdesc_update_login_options = array("v", "", "login_options");
	static $__acdesc_update_demo_status = array("v", "", "demo_status");
	static $__acdesc_update_upload_logo = array("v", "", "upload_logo");

	function isRightParent()
	{
		return true;
	}

	function getLanguage()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$list = lscandir_without_dot(getreal("/lang"));

		foreach ($list as $l) {
			if (isset($sgbl->__var_language[$l])) {
				$fl[$l] = $sgbl->__var_language[$l];
			}
		}

		return $fl;
	}

	function updateform($subaction, $param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$progname = $sgbl->__var_program_name;

		switch ($subaction) {
			case "skin":
				// ACtually the skin_color list should be dependent on the skin_name,
				// but currently just reading the current skin directory itself.
				// So all the skins should have the same color sets, which is not very practical,
				// so this should be changed in the future.

				if (!$this->getParentO()->isLogin() || $this->isClass('sp_childspecialplay')) {
					$vlist['specialplay_b-dont_show_disabled_permission'] = null;
				}

				$vlist['specialplay_b-enable_ajax'] = null;
				$vlist['specialplay_b-simple_skin'] = null;

				if ($this->specialplay_b->skin_name === 'feather') {
					$vlist['specialplay_b-show_thin_header'] = null;
				}

				$vlist['specialplay_b-close_add_form'] = null;

				if (!$this->isAdmin()) {
					$list = get_namelist_from_objectlist($login->getList('interface_template'));
					$list = lx_array_merge(array(array("--$progname-default--"), $list));
				}

				//	$vlist['specialplay_b-interface_template'] = array('s', $list);
				$vlist['specialplay_b-skin_name'] = array('s', lscandir_without_dot(getreal("/img/skin/$progname")));


				$vlist['specialplay_b-skin_color'] = array('s', lscandir_without_dot(getreal("/img/skin/$progname/" . $this->specialplay_b->skin_name)));
				$vlist['specialplay_b-icon_name'] = array('s', array('collage'));
				$vlist['specialplay_b-language'] = array('A', $this->getLanguage());
				//	$vlist['specialplay_b-login_page'] =array('s', $this->getParentO()->getLoginTo());
				//	$vlist['specialplay_b-split_frame'] = null;
				//	$vlist['specialplay_b-show_help'] =null;

				if ($this->getParentO()->isLte('reseller') && $sgbl->isKloxo()) {
					$vlist['specialplay_b-customermode_flag'] = null;
				}

				//	$vlist['specialplay_b-disable_quickaction'] = null;

				if (!$this->getParentO()->isLogin()) {
					$vlist['specialplay_b-logo_image'] = null;
					$vlist['specialplay_b-logo_image_loading'] = null;
				}

				/*
				  if ($sgbl->isKloxo()) {
					  if (!$this->getParentO()->isLogin()) {
						  $vlist['specialplay_b-disable_docroot'] = null;
					  }
				  }
			 */

				//	$vlist['specialplay_b_s_show_add_buttons'] =null;
				//	$vlist['specialplay_b-lpanel_scrollbar'] = null;
				//	$vlist['specialplay_b-resource_bottom'] =null;
				//	$vlist['specialplay_b_s_show_brethren_list'] = array('s', array('off', 'top', 'left'));
				//	$vlist['specialplay_b_s_lpanel_group_resource'] =null;
				//	$vlist['specialplay_b_s_ultra_navig'] =null;
				//	$vlist['specialplay_b-lpanel_depth'] = null;
				$vlist['__v_updateall_button'] = array();
				//	$vlist['specialplay_b_s_per_page'] = null;

				break;

			case "upload_logo":
				if ($login->priv->isOn('logo_manage_flag')) {
					//	$vlist['specialplay_b-logo_image'] =array('I', array("width" => 20, "height" => 20,
					//		"value" => $this->specialplay_b->logo_image));
					// trick use 'null' for guarantee 100% size of img (not 100% size div container)
					$vlist['specialplay_b-logo_image'] = array('I', array("width" => "null", "height" => "null", "value" => "/img/user-logo.png"));
					$vlist['logo_image_f'] = null;
					//	$vlist['specialplay_b-logo_image_loading'] =array('I', array("width" => 20, "height" => 20,
					//		"value" => $this->specialplay_b->logo_image_loading));
					//	$vlist['logo_image_loading_f'] = null;
				}

				break;

			case "login_options":
				if ($login->isAdmin()) {
					$gen = $login->getObject('general')->generalmisc_b;
					$this->specialplay_b->disableipcheck = $gen->disableipcheck;
					$vlist['specialplay_b-disableipcheck'] = null;
				}

				$vlist['specialplay_b-ssession_timeout'] = null;

				break;

			case "demo_status":
				$vlist['specialplay_b-demo_status'] = null;

				break;
		}

		return $vlist;
	}

	function createShowPropertyList(&$alist)
	{
	}

	function updatelogin_options($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if ($login->isAdmin()) {
			$gg = $login->getObject('general');
			$gen = $gg->generalmisc_b;
			$gen->disableipcheck = $param['specialplay_b-disableipcheck'];
			$gg->setUpdateSubaction();
			$gg->write();
		}

		if (intval($param['specialplay_b-ssession_timeout']) < 100) {
			$param['specialplay_b-ssession_timeout'] = 100;
		}

		return $param;
	}

	function updateupload_Logo($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// MR -- prevent for 'upload logo' process on demo mode
		if (if_demo()) {
			return null;
		}

		$progname = $sgbl->__var_program_name;
		$parent = $this->getParentO();
		$imgname = $parent->getClName();
		//	$param['specialplay_b-logo_image'] = "/img/logo/$imgname.gif";
		//	$param['specialplay_b-logo_image_loading'] = "/img/logo/$imgname-loading.gif";
		//	make_sure_directory_is_lxlabs("__path_program_htmlbase/img/logo");

		$param['specialplay_b-logo_image'] = "/img/user-logo.png";

		$fullpath_logo_image = __path_program_htmlbase . $param['specialplay_b-logo_image'];

		// temporary only for admin - 6.1.7
		if ($_FILES['logo_image_f']['tmp_name']) {
			lxfile_mv($_FILES['logo_image_f']['tmp_name'], $fullpath_logo_image);
		}
		/*
		 else {
			 lxfile_cp("__path_program_htmlbase/img/$progname-logo.gif", "__path_program_htmlbase" . $param['specialplay_b-logo_image']);
		 }
	 */
		lxfile_cp($fullpath_logo_image, "/usr/local/lxlabs/kloxo/file/user-logo.png");
		// must chown to lxlabs for successful display on 'Upload Logo'
		lxfile_unix_chown($fullpath_logo_image, "lxlabs");
		exec("lxphp.exe /usr/local/lxlabs/kloxo/bin/fix/fix-userlogo.php --select=all");

		/*
		 if ($_FILES['logo_image_loading_f']['tmp_name']) {
			 lxfile_mv($_FILES['logo_image_loading_f']['tmp_name'],
				  "__path_program_htmlbase" .$param['specialplay_b-logo_image_loading']);
		 } else {
			 lxfile_cp("__path_program_htmlbase/img/$progname-splash.gif",
				 "__path_program_htmlbase" . $param['specialplay_b-logo_image_loading']);
		 }
	 */
		$tsp = $parent->getObject("sp_childspecialplay");
		$tsp->specialplay_b->logo_image = $param['specialplay_b-logo_image'];
		//	$tsp->specialplay_b->logo_image_loading = $param['specialplay_b-logo_image_loading'];
		$tsp->setUpdateSubaction('upload_logo');
		$this->setUpdateSubaction('upload_logo');

		return $param;
	}


	function postUpdate()
	{
		// Hack Hack Hack... Redirecting the whole frame thing after a skin change...
		// It is supposed to be handled by the display.php, but since this is a single case, i am doing it here...
		global $gbl, $sgbl, $login, $ghtml;

		$gbl->setSessionV('show_lpanel', $login->getSpecialObject('sp_specialplay')->show_lpanel);
		$gbl->setSessionV('show_help', $login->getSpecialObject('sp_specialplay')->show_help);

		if (if_demo()) {
			throw new lxexception('not_allowed_in_demo', '');
		}

		if ($this->subaction === 'skin' && !$this->isClass('sp_childspecialplay')) {
			if ($sgbl->dbg < 0 && $this->getParentO()->isLogin()) {
				$login->was();
				if ($ghtml->frm_consumedlogin !== 'true') {
					$ghtml->print_redirect_self("/");
					exit;
				}
			}
		}
	}

	function updateSkin($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$progname = $sgbl->__var_program_name;

		$sk = $param['specialplay_b-skin_name'];
		$skc = $param['specialplay_b-skin_color'];

		if (!lxfile_exists("img/skin/$progname/$sk/$skc")) {
			$param['specialplay_b-skin_color'] = 'default';
		}

		return $param;
	}
}


class sp_childSpecialPlay extends sp_basespecialplay
{
	static $__acdesc_update_skin = array("", "", "child_appearance");
	static $__special_class = "specialplay";
}

class sp_SpecialPlay extends sp_basespecialplay
{
	static $__acdesc_update_skin = array("", "", "appearance");
}
