<?php

include_once "lib/php/coreffilelib.php";

class Ffile extends Lxclass
{
	// Core
	static $__ttype = "transient";
	static $__desc = array("PN", "", "file");

	// Data
	static $__desc_nname = array("", "", "file_name", "a=show");
	static $__desc_description = array("", "", "file_name", "a=show");
	static $__desc_fullpath = array("", "", "file_name");
	static $__desc_base = array("", "", "file_name");
	static $__desc___parent_o = array("", "", "parent");
	static $__desc_ttype = array("e", "", "t", "a=show");
	static $__desc_ttype_v_back = array("", "", "previous");
	static $__desc_ttype_v_trash = array("", "", "trash");
	static $__desc_ttype_v_directory = array("", "", "directory");
	static $__desc_ttype_v_link = array("", "", "link");
	static $__desc_ttype_v_dirlink = array("", "", "directory_link");
	static $__desc_ttype_v_filelink = array("", "", "file_link");
	static $__desc_ttype_v_brokenlink = array("", "", "broken_link");
	static $__desc_ttype_v_directory_copy = array("", "", "directory_(copy)");
	static $__desc_ttype_v_zip = array("", "", "zip");
	static $__desc_ttype_v_tgz = array("", "", "tgz");
	static $__desc_ttype_v_tbz2 = array("", "", "tbz2");
	static $__desc_ttype_v_gz = array("", "", "gz");
	static $__desc_ttype_v_bz2 = array("", "", "bz2");
	static $__desc_ttype_v_txz = array("", "", "txz");
	static $__desc_ttype_v_xz = array("", "", "xz");
	static $__desc_ttype_v_p7z = array("", "", "p7z");
	static $__desc_ttype_v_rar = array("", "", "rar");
	static $__desc_ttype_v_tar = array("", "", "tar");
	static $__desc_ttype_v_directory_cut = array("", "", "directory_(cut)");
	static $__desc_ttype_v_file = array("", "", "file");
	static $__desc_ttype_v_file_copy = array("", "", "file_(copy)");
	static $__desc_ttype_v_file_cut = array("", "", "file_(cut)");
	static $__desc_ttype_v_ahtml = array("", "", "file_(html)");
//	static $__desc_protect = array("e", "",  "p", "__int|goback=1&a=show&l[class]=dirprotect&l[nname]=hello");
	static $__desc_protect = array("e", "", "p");
	static $__desc_protect_v_on = array("", "", "protected");
	static $__desc_protect_v_off = array("", "", "not_protected");
	static $__desc_protect_v_na = array("", "", "not_applicable");
	static $__desc_mode = array("", "", "perm", "a=updateform&sa=perm");
	static $__desc_uid = array("", "", "user");
	static $__desc_content = array("t", "", "file");
	static $__desc___username_o = array("", "", "user_name");
//	static $__desc_other_username = array("", "", "owner");
	static $__desc_other_username = array("", "", "owner", "a=updateform&sa=own");
	static $__desc_pvrename = array("b", "", "ren", "a=updateform&sa=rename");
	static $__desc_pvrename_v_rename = array("", "", "rename");
	static $__desc_pvdownload = array("b", "", "dn", "a=update&sa=download");
	static $__desc_pvdownload_v_download = array("", "", "download");
	static $__desc_gid = array("", "", "file_name");
	static $__desc_mtime = array("", "", "modified");
	static $__desc_atime = array("", "", "file_name");
	static $__desc_size = array("", "", "Size");
	static $__desc_realsize = array("", "", "real_size");
	static $__desc_diskspace = array("", "", "disk_space");
	static $__desc_zipcontent = array("T", "", "contents_of_the_archive");
	static $__desc_sizeper = array("p", "", "%_of_tot");

	static $__desc_download_ftp_f = array("n", "", "ftp_server");
	static $__desc_download_ftp_file_f = array("n", "", "file_name");
	static $__desc_download_username_f = array("n", "", "username");
	static $__desc_download_password_f = array("n", "", "password");
	static $__desc_download_url_f = array("n", "", "upload_from_url");
	static $__desc_download_overwrite_f = array("f", "", "overwrite_file");
	static $__desc_file_permission_f = array("", "", "size_(k)");
	static $__desc_file_ownership_f = array("", "", "size_(k)");
	static $__desc_new_name_f = array("", "", "new_name");
	static $__desc_newfolder_f = array("", "", "folder_name");
	static $__desc_newfile_f = array("", "", "file_name");
	static $__desc_zip_file_f = array("", "", "zip_file_name");
	static $__desc_upload_file_f = array("F", "", "upload_file");
	static $__desc_upload_overwrite_f = array("f", "", "overwrite_existing_file");
	static $__desc_zip_overwrite_f = array("f", "", "overwrite_existing_files");
	static $__desc_zip_extract_dir_f = array("", "", "extract_zip_to_here");
	static $__desc_extract_to_tar_f = array("f", "", "extract_to_tar_file");
	static $__desc_fake_f = array("", "", "");
	static $__desc_new_format_f = array("", "", "convert_to_format");

	static $__desc_image_content = array("", "", "image");
	static $__desc_image_height = array("", "", "height");
	static $__desc_image_width = array("", "", "width");
	static $__desc_image_type = array("", "", "type");
	static $__desc_copy_old_image_flag_f = array("f", "", "keep_a_copy_of_the_image");
	static $__desc_old_image_name_f = array("", "", "copy_name");

	//  lists
	static $__desc_ffile_l = array("R", "", "file_name");

	static $__acdesc_update_rename = array("", "", "rename");
	static $__acdesc_update_paste = array("", "", "paste");
	static $__acdesc_update_paste_inactive = array("", "", "paste");
	static $__acdesc_update_download_from_url = array("", "", "upload_from_url");
	static $__acdesc_update_download_from_http = array("", "", "upload_(http)");
	static $__acdesc_update_download_from_ftp = array("", "", "upload_(ftp)");
	static $__acdesc_update_copy = array("", "", "copy");
	static $__acdesc_update_fancyedit = array("", "", "html_edit");
	static $__acdesc_update_cut = array("", "", "cut");
	static $__acdesc_update_perm = array("", "", "change_permissions");
	static $__acdesc_update_own = array("", "", "change_ownership");
	static $__acdesc_update_newdir = array("", "", "newdir");
	static $__acdesc_update_newfile = array("", "", "newfile");
	static $__acdesc_update_filedelete = array("", "", "trash");
	static $__acdesc_update_filerealdelete = array("", "", "Remove");
	static $__acdesc_update_go_home = array("", "", "home");
	static $__acdesc_update_go_up = array("", "", "up");
	static $__acdesc_update_restore_trash = array("", "", "restore");
	static $__acdesc_update_zip_file = array("", "", "zip");
	static $__acdesc_update_clear_trash = array("", "", "clear");
	static $__acdesc_update_unzip = array("", "", "unzip");
	static $__acdesc_update_thumbnail = array("", "", "thumbnail");
	static $__acdesc_update_convert_image = array("", "", "convert_format");
	static $__acdesc_update_diskspace = array("", "", "disk_usage");
	static $__acdesc_update_toggle_dot = array("", "", "t_hidden");
	static $__acdesc_update_upload = array("", "", "upload");
	static $__acdesc_update_upload_s = array("", "", "upload");
	static $__acdesc_update_zipextract = array("", "", "extract_archive");
	static $__acdesc_update_content = array("", "", "contents");
	static $__acdesc_update_edit = array("", "", "edit");
	static $__acdesc_update_archive = array("", "", "browse_archive");
	static $__acdesc_update_backupftpupload = array("", "", "ftp_upload");

	static $__acdesc_show = array("", "", "file_manager");
	static $__acdesc_update_download = array("", "", "download");

	function __construct($masterserver, $readserver, $root, $name, $__username_o)
	{
		global $sgbl;

		// MR -- trouble if $root with format '__path_' and then converted!
		if (strpos($root, '__path_') !== false) {
			$a = explode('/', $root);
			$a[0] = $sgbl->$a[0];
			$this->root = implode('/', $a);
		} else {
			$this->root = $root;
		}

		$this->__username_o = $__username_o;

		parent::__construct($masterserver, $readserver, $name);
	}

	function getFullPath()
	{
		$this->fullpath = preg_replace('/(\/){1,3}(.*)/', '/$2', "{$this->root}/{$this->nname}");
		return $this->fullpath;
	}

	function isButton($name)
	{
		if ($this->base === ".trash") {
			return false;
		}

		return true;
	}

	function getMultiUpload($var)
	{
		if ($var === 'upload') {
			return array('upload_s', 'download_from_http', 'download_from_ftp');
		}

		return $var;
	}

	function isSelect()
	{
		if ($this->base === '__backup') {
			return false;
		}
		if ($this->base === "..") {
			return false;
		}

		if ($this->base === ".trash") {
			return false;
		}

		return true;
	}

	function isAction($var)
	{
		// hackhack Using ghtml here itself.
		global $gbl, $sgbl, $login, $ghtml;

		if ($ghtml->frm_action === 'selectshow') {
			if (!$this->is_dir()) {
				return false;
			}
		}

		if ($this->base === '__backup' && $var === 'pvrename') {
			return false;
		}

		if ($this->isInsideTrash()) {
			return false;
		}

		if ($var === "protect") {
			if ($this->protect === "na") {
				return false;
			}
		}

		if ($var === 'pvdownload') {
			if ($this->is_dir()) {
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	static function perPage()
	{
		return 300;
	}

	function print_back()
	{
?>
	<table width='90%'>
		<tr>
			<td>
				<a href="<?php echo $_SERVER['PHP_SELF'] ?>?frm_action=show&frm_o_nname=<?php echo dirname($this->nname) ?>"> Back </a>
			</td>
		</tr>
	</table>
<?php
	}

	function isDisplay($fpath = NULL)
	{
		static $dot;

		global $gbl, $sgbl, $login, $ghtml;

		if (!$dot) {
			$dot = $gbl->getSessionV('frm_show_dot');
		}

		if ($this->base === "..") {
			return false;
		}

		if (csb($this->base, ".__trash")) {
			return false;
		}

		if (!isOn($dot)) {
			if (csb($this->base, ".") && !csa($this->base, ".trash")) {
				return false;
			}
		}

		return true;
	}

	static function searchVar()
	{
		return "base";
	}

	function write() { }

	function createShowClist($subaction)
	{
		if ($this->is_dir()) {
			$clist['ffile'] = null;
			return $clist;
		} else {
			return null;
		}
	}

	function updateUpload_s($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$this->up_temp_file_name = $_FILES['upload_file_f']['tmp_name'];
		$this->__var_upload_filepass = cp_fileserv($this->up_temp_file_name);
		$this->__var_upload_tmp_server = getOneIPForLocalhost($this->syncserver);
		$this->upload_file_name = $_FILES["upload_file_f"]["name"];

		if (!$this->upload_file_name) {
			throw new lxException($login->getThrow('no_file_in_upload'), '', $this->upload_file_name);
		}

		$this->upload_file_name = str_replace("'", "", $this->upload_file_name);
		$this->setUpdateSubaction('upload_s');
		$gbl->__this_redirect = $this->getCurDirUrl();
		$this->upload_overwrite_f = $param['upload_overwrite_f'];

		return null;
	}

	function updateDownload($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gen = $login->getObject('general')->generalmisc_b;

		ignore_user_abort(false);
		log_log("download", "ignored user abort");
		$this->download_f = true;
		$this->get();
		$ret = $this->serverfile_data;

		while (@ob_end_clean()) {
			// no action
		}

		if ($this->isLocalhost('__readserver') || $gen->isOn('masterdownload')) {
			header("Content-Disposition: attachment; filename=$this->base");
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $this->size");
			printFromFileServ($this->__readserver, $ret);

			flush();

			exit;
		} else {
			$url = getFQDNforServer($this->__readserver);
			$ret['realname'] = $this->base;
			$ob = new Remote();
			$ob->filepass = $ret;
			$var = base64_encode(serialize($ob));
			$url = "http://$url:{$sgbl->__var_prog_port}/theme/lbin/filedownload.php?frm_info=$var";
			header("Location: $url");
		//	$ghtml->print_redirect($url);

			exit;
		}
	}

	function updateRename($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$this->newname = $param['new_name_f'];
		$this->setUpdateSubaction('rename');
		$gbl->__this_redirect = $this->getParentDirUrl();

		return null;
	}

	function updateRestore_trash($param)
	{
		$this->updateAccountSel($param, 'restore_trash');

		return null;
	}

	function updateclear_trash($param)
	{
		$this->updateAccountSel($param, 'clear_trash');

		return null;
	}

	function updatezipextract($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gbl->__this_redirect = $this->getDirUrl($param['zip_extract_dir_f']);

		return $param;
	}

	function updatezip_file($param)
	{
		$this->updateAccountSel($param, 'zip_file');

		return null;
	}

	function Updatefiledelete($param)
	{
		$this->updateAccountSel($param, 'filedelete');

		return null;
	}

	function Updatefilerealdelete($param)
	{
		$this->updateAccountSel($param, 'filerealdelete');

		return null;
	}

	function updatePaste($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$list = $gbl->getSessionV("frm_clip_list");
		$this->pasteaction = $gbl->getSessionV("frm_clip_action");
		$this->pasteserver = $gbl->getSessionV("frm_clip_server");
		$this->pasteroot = $gbl->getSessionV("frm_clip_root");

		if ($this->pasteserver !== $this->syncserver) {
			$filepass = rl_exec_get(null, $this->pasteserver, "cp_fileserv_list", array($this->pasteroot, $list));
		}

		$this->pasteserver_realip = getFQDNforServer($this->pasteserver);

		$fflist = $this->getList("ffile");

		foreach ($list as $l) {
			$baselist[] = basename($l);
		}

		$error_exist = NULL;

		foreach ((array)$fflist as $ff) {
			if (array_search_bool($ff->base, $baselist)) {
				$error_exist .= "$ff->base, ";
				$list = array_remove($list, $ff->nname);
			}
		}

		if ($error_exist) {
			$error_exist = preg_replace("/, $/", "", $error_exist);
			$error_exist .= "&frm_emessage=file_exists";
			$error_exist .= "&frm_m_emessage_data=$error_exist";
			$gbl->__this_redirect = $this->getCurDirUrl();
			$gbl->__this_redirect .= $error_exist;
		}

		$this->paste_list = $list;
		$this->filepass = $filepass;
		$this->setUpdateSubaction('paste');

		return null;
	}

	function updateToggle_dot($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$v = $gbl->getSessionV("frm_show_dot");

		if (isOn($v)) {
			$v = 'off';
		} else {
			$v = 'on';
		}

		$gbl->setSessionV("frm_show_dot", $v);
	}

	function updateDiskSpace($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gbl->setSessionV("ffile_duflag", true);
		$gbl->__no_debug_redirect = true;
		$gbl->__this_redirect = $this->getCurDirUrl();

		return null;
	}

	function updateCopyOrCut($param, $action)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$gbl->setSessionV("frm_clip_action", $action);
		$flist = $param['_accountselect'];

		foreach ($flist as $ff) {
			$fpathlist[] = $ff;
		}

		$this->dbaction = "clean";
		$gbl->setSessionV("frm_clip_list", $fpathlist);
		$gbl->setSessionV("frm_clip_server", $this->syncserver);
		$gbl->setSessionV("frm_clip_root", $this->root);
		$gbl->setSessionV("frm_clip_rootname", $this->getParentO()->nname);

		return null;
	}

	static function getTextAreaProperties($var)
	{
		return array("height" => 30, "width" => "90%");
	}

	function updateCopy($param)
	{
		$this->updateCopyOrCut($param, "copy");

		return null;
	}

	function updateCut($param)
	{
		$this->updateCopyOrCut($param, "cut");

		return null;
	}

	function isShowHeader()
	{
		return $this->__flag_showheader;
	}

	function showRawPrint($subaction = null)
	{
		if ($this->isShowHeader()) {
			$this->printFullUrl();
		}
	}

	function printFullUrl()
	{
		// Big freeking hack.
		global $gbl, $sgbl, $login, $ghtml;

		$genimgpath = get_general_image_path();

		$iconpath = get_image_path();

		$skin_name = $login->getSpecialObject('sp_specialplay')->skin_name;
		$button_type = $login->getSpecialObject('sp_specialplay')->button_type;

		if ($button_type !== 'image') {
			$background = "url({$genimgpath}/button/fnav_02.gif)";
			$icon_text = "<span class='if16' style='color:#d33'>&#xf382;</span>";
		} else {
			$background = "#ffffff";
			$icon_text = "<img height='16' src='{$iconpath}/ffile_show.gif'>";
		}

		// Hack hack

		if ($skin_name === 'simplicity') {
		} else {
		}
?>

<div style="width:910px; margin:0 auto">
<table>
	<tr>
		<td><?=$icon_text;?></td>
		<td nowrap style='background <?= $background ?>'>
<?php

		$base = $ghtml->frm_selectshowbase;
		
		if ($ghtml->isSelectShow()) {
			$url = "a=selectshow&l[class]=ffile&l[nname]=/$base";
		} else {
			$url = "a=show&l[class]=ffile&l[nname]=/";
		}

		$url = $ghtml->getFullUrl($url);
		
		if ($base) {
			$url .= "&frm_selectshowbase=$base";
		}

		$parent = $this->getParentO();
		$desc = get_classvar_description($parent->getClass());
?>
		Address: (<?= $desc[2] ?>: <a class=insidelist href="<?= $url ?>"><?= $parent->getId() ?></a>)
<?php
		$list = explode('/', $this->nname);
	//	implode('/', $list);
		
		for ($j = 0; $j < count($list); $j++) {
			$nlist[] = $list[$j];
			$newname = implode('/', $nlist);
			
			if ($base) {
				if (strlen($newname) < strlen($base)) {
					continue;
				}
			}

			if ($base) {
				$url = 'a=selectshow&l[class]=ffile&l[nname]=' . $newname;
			} else {
				$url = 'a=show&l[class]=ffile&l[nname]=' . $newname;
			}

			$url = $ghtml->getFullUrl($url);
			
			if ($base) {
				$url .= "&frm_selectshowbase=$base";
			}
			if ($j === count($list) - 1) {

?>
<span>/ <?= $list[$j] ?></span>

<?php
			} else {
				if ($list[$j] !== '') {
?>
<span>/ <a #ff0000" class="insidelist" href="<?= $url ?>"><?= $list[$j] ?></a></span>
<?php
				}
			}
		}
?>
		</td>
		<td width="100%"></td>
	</tr>
</table>
</div>
<br/>
<?php
		$list = $gbl->getSessionV("frm_clip_list");

		if ($list) {
			$file = implode(", ", $list);

			if (strlen($file) > 30) {
				$file = substr($file, 0, 30) . " ...";
			}
?>
<div class="div_showhide">
	<table cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td width="100%" align="center">Global Clipboard - (<?= $gbl->getSessionV("frm_clip_rootname") ?>): <?= $file ?></td>
		</tr>
	</table>
</div>
<?php
		}
	}

	function getCurDirUrl()
	{
		return $this->getDirUrl($this->nname);
	}

	function getParentDirUrl()
	{
		return $this->getDirUrl(dirname($this->nname));
	}

	function getDirUrl($dir)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$url = "a=show&l[class]=ffile&l[nname]=$dir";
		$url = $ghtml->getFullUrl($url);
		
		return $url;
	}

	function updatePerm($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// Hack Hack.. This should be done by display.php itself. But permissions are now handled separately..
		$this->setUpdateSubaction('perm');

		$this->recursive_f = $param['recursive_f'];
		$this->target_f = $param['target_f'];
		$this->newperm = $param['file_permission_f'];

		$gbl->__this_redirect = $this->getParentDirUrl();

		return null;
	}

	function updateOwn($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		// Hack Hack.. This should be done by display.php itself. But permissions are now handled separately..
		$this->setUpdateSubaction('own');

		$this->recursive_f = $param['recursive_f'];
		$this->user_f = $param['user_f'];
		$this->group_f = $param['group_f'];
		$this->newown = $param['file_ownership_f'];

		$gbl->__this_redirect = $this->getParentDirUrl();

		return null;
	}

	function updateNewDir($param)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (strpos($param['newfolder_f'], '../') !== false) {
			throw new lxException($login->getThrow("folder_name_may_not_contain_doubledotsslash"), '', $param['newfolder_f']);
		}

		$this->setUpdateSubaction('newdir');
		$this->newfolder_f = $param['newfolder_f'];
		$gbl->__this_redirect = $this->getDirUrl($this->nname);

		return null;
	}

	function updateNewFile($param)
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		if (strpos($param['newfile_f'], '../') !== false) {
			throw new lxException($login->getThrow("file_name_may_not_contain_doubledotsslash"), '', $param['newfile_f']);
		}

		$this->newfile_f = $param['newfile_f'];

		$file = "$this->nname/$this->newfile_f";

		$url = 'a=updateForm&sa=edit&l[class]=ffile&l[nname]=' . $file;
		$url = $ghtml->getFullUrl($url);
		$gbl->__this_redirect = $url;

		return null;
	}

	function show_content($param)
	{
		$this->getContent();

		if (coreFfile::is_image($this->nname)) {
			remove_if_older_than_a_minute_dir("tmp");
			$thumb = tempnam("tmp/", "image");
			lfile_put_contents($thumb, $this->image_content);
			lxfile_generic_chmod($thumb, "0755");
			$vlist['image_content'] = array('I', array("width" => 50, "height" => 50, "value" => "$thumb"));
			$vlist['image_width'] = null;
			$vlist['image_height'] = null;
		//	$vlist['image_type'] = null;
			$vlist['copy_old_image_flag_f'] = null;
			$dir = dirname($this->nname);
			$name = basename($this->nname);
			$vlist['old_image_name_f'] = array('m', "$dir/copy-$name");
			$vlist['__v_button'] = "Resize";
		} else {
			if ($this->isOn('not_full_size')) {
				$vlist['fake_f'] = array('M', "Showing only the last {$this->numlines} KiloBytes of the file");
			}

			$vlist['content'] = null;
			$vlist['__v_button'] = "";
		}

		return $vlist;
	}

	function updateform($subaction, $param)
	{
		$vlist = null;

		switch ($subaction) {
			case "thumbnail":
				$this->image_width = '20';
				$this->image_height = '20';
				$vlist['image_width'] = null;
				$vlist['image_height'] = null;

				break;

			case "convert_image":
				$extlist = array('gif' => 'gif', 'jpg' => 'jpg', 'png' => 'png');
				$ext = coreFfile::getExtension($this->nname);
				unset($extlist[$ext]);
				$vlist['nname'] = array('M', null);
				$vlist['new_format_f'] = array('s', $extlist);

				break;

			case "diskspace":
				$vlist['diskspace'] = array('M', "calculate disk space");
				$vlist['__v_button'] = 'calculate disk space';

				break;

			case "zip_file":
				$vlist['zip_file_f'] = null;

				break;

			case "newdir":
				$vlist['newfolder_f'] = null;

				break;

			case "newfile":
				$vlist['newfile_f'] = null;

				break;

			case "content":
				return $this->show_content($param);

			case "fancyedit":
				$this->getContent();

				if ($this->isOn('not_full_size')) {
					$vlist['fake_f'] = array('M', "File Too Large to Edit");
					$vlist['__v_button'] = array();
				} else {
					$vlist['content'] = array('V', $this->content);
					$vlist['__v_button'] = array();
				}

				break;

			case "edit":
				$this->getContent();
				
				if ($this->isOn('not_full_size')) {
					$vlist['fake_f'] = array('M', "File Too Large to Edit");
					$vlist['__v_button'] = array();
				} else {
					$vlist['content'] = null;
					$vlist['__v_button'] = "Save";
				}

				break;

			case "zipextract":
				$this->getContent();
				$vlist['zipcontent'] = null;
				$vlist['zip_extract_dir_f'] = array('m', dirname($this->nname));
			
			//	$vlist['zip_overwrite_f'] = null;

				if (($this->ttype === 'tgz')
						|| ($this->ttype === 'tbz2')
						|| ($this->ttype === 'txz')) {

					$vlist['extract_to_tar_f'] = null;
				}

				$vlist['__v_button'] = "Extract";

				break;

			case "perm":
				$vlist['file_permission_f'] = array();
				$vlist['target_f'] = array();
				$vlist['recursive_f'] = array();

				break;

			case "own":
				$vlist['file_ownership_f'] = array();
				$vlist['user_f'] = array();
				$vlist['group_f'] = array();
				$vlist['recursive_f'] = array();

				break;

			case "rename":
				$vlist['new_name_f'] = array('m', basename($this->nname));
				$vlist['__v_button'] = "Rename";

				break;

			case "upload_s":
				$vlist['upload_file_f'] = null;
				$vlist['upload_overwrite_f'] = null;
				$vlist['__v_button'] = "Upload";
				
				break;

			case "download_from_ftp":
				$vlist['download_ftp_f'] = null;
				$vlist['download_username_f'] = null;
				$vlist['download_password_f'] = null;
				$vlist['download_ftp_file_f'] = null;
				$vlist['download_overwrite_f'] = null;
				$vlist['__v_button'] = "Upload";

				break;

			case "backupftpupload":
				$this->downloadFromBackup($vlist);

				break;

			case "download_from_http":
				$vlist['download_url_f'] = null;
				$vlist['download_overwrite_f'] = null;
				$vlist['__v_button'] = "Upload";

				break;
		}

	//	dprint($subaction);

		return $vlist;
	}

	function downloadFromBackup(&$vlist)
	{
		global $login;

		$parent = $this->getParentO();

		if (!$parent->isClass('lxbackup')) {
			throw new lxException($login->getThrow('only_in_backup'));
		}

		if (!$parent->ftp_server) {
			throw new lxException($login->getThrow('ftp_server_not_set'));
		}

		$fn = lxftp_connect($parent->ftp_server);
		$mylogin = ftp_login($fn, $parent->rm_username, $parent->rm_password);

		if ($parent->rm_directory) {
			ftp_chdir($fn, $parent->rm_directory);
		}
		$list = ftp_nlist($fn, ".");

		$vlist['download_ftp_f'] = array('M', $parent->ftp_server);
		$vlist['download_username_f'] = array('M', $parent->rm_username);
		$vlist['download_ftp_file_f'] = array('s', $list);
		$vlist['__v_button'] = "Upload";
	}

	function getExtraId()
	{
		if (isset($this->__var_extraid)) {
			return $this->__var_extraid;
		} else {
			return null;
		}
	}

	function getId()
	{
		if (isset($this->__var_extraid)) {
			return strfrom($this->__var_extraid, "__lx_");
		} else {
			if (csa($this->nname, "_s_vv_p_")) {
				return strfrom($this->nname, "_s_vv_p_");
			} else {
				$pdesc = get_classvar_description($this->getParentO()->getClass());

			//	return "$pdesc[2]: {$this->getParentO()->getId()} $this->nname";
				return "$this->nname";
			}
		}
	}

	function getContent()
	{
	//	$stat['mode'] = $this->mode;
		$stat['ttype'] = $this->ttype;
	//	$stat['ttype'] = os_getZipType($this->getFullPath());

		$stat = rl_exec_get(null, $this->__readserver, array("coreFfile", "getContent"), array($this->__username_o, $this->root, $this->getFullPath(), $stat, $this->numlines));

		$this->modify($stat);
		$this->dbaction = 'clean';
	}

	function createShowPropertyList(&$alist)
	{
		if ($this->base === ".trash") {
			$alist['property'][] = 'a=show';

			return null;
		}

		if ($this->base === "__backup" || basename($this->root) === "__backup") {
			$this->getParentO()->createShowPropertyList($alist);
			
			foreach ($alist['property'] as &$__a) {
				$__a = "goback=1&$__a";
			}

			return $alist;
		}

		if ($this->isOn('browsebackup')) {
			$alist['property'][] = "goback=1&a=show";
			$alist['property'][] = "a=show";

			return $alist;
		}

		if ($this->getParentO()->isClass('mailinglist')) {
			$this->getParentO()->createShowPropertyList($alist);
			
			foreach ($alist['property'] as &$__a) {
				if (is_object($__a)) {
					$__a->url = "goback=1&$__a->url";
				} else {
					$__a = "goback=1&$__a";
				}
			}
			
			return $alist;
		}

		$alist['property'][] = 'a=show';

		$alist['property'][] = "a=updateform&sa=perm";
		$alist['property'][] = "a=updateform&sa=own";

		if ($this->nname !== '/') {
			$alist['property'][] = "a=updateform&sa=rename";
		}

		if ($this->isOn('readonly')) {
			if (!$this->is_dir()) {
				$alist['property'][] = "a=update&sa=download";
			} else {
				$alist['property'][] = "a=update&sa=diskspace";
			}

			return $alist;
		}

		if ($this->is_dir()) {
			$alist['property'][] = "a=update&sa=diskspace";
			$alist['property'][] = "a=updateform&sa=upload";
			$alist['property'][] = "a=updateForm&sa=thumbnail";
		} else {
			if ($this->is_image()) {
				$alist['property'][] = "a=updateForm&sa=convert_image";
			} else if (!$this->is_zip()) {
				$alist['property'][] = "a=updateForm&sa=edit";
				$alist['property'][] = "a=updateForm&sa=fancyedit";
			}

			$alist['property'][] = "a=update&sa=download";
		}

		if (!$this->is_top()) {
		//	$alist['property'][] = "a=updateform&sa=rename";
		//	$alist['property'][] = "a=updateform&sa=perm";
		}
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		if ($this->base === ".trash") {
			return null;
		}

		return $alist;
	}

	function createShowUpdateform()
	{
		if ($this->is_dir()) {
			return null;
		} else if ($this->is_zip()) {
			$uflist['zipextract'] = null;
			return $uflist;
		} else {
			$uflist['content'] = null;
			return $uflist;
		}
	}


	function getDomainName()
	{
		list($dom,) = explode('/', $this->nname, 2);

		return $dom;
	}

	function getRest()
	{
		if (strpos($this->nname, '/') !== false) {
			list($dom, $rest) = explode('/', $this->nname, 2);
			return $rest;
		}

		return "";
	}

	function is_link()
	{
		if ($this->mode & 0120000) {
			return true;
		}

		return false;
	}

	function is_image()
	{
		return coreFfile::is_image($this->nname);
	}

	function is_zip()
	{
		if ($this->ttype === 'zip' || $this->ttype === 'tar' 
				|| $this->ttype === 'tgz' || $this->ttype === 'tbz2'
				|| $this->ttype === 'gz'  || $this->ttype === 'bz2'
				|| $this->ttype === 'txz' || $this->ttype === 'xz'
				|| $this->ttype === 'p7z' || $this->ttype === 'rar') {
			return true;
		}

		return false;
	}

	function is_dirlink()
	{
		return ($this->ttype === 'dirlink');
	}

	function is_dir()
	{
		return ($this->ttype === 'directory' || $this->ttype === 'dirlink' || $this->ttype === 'trash');

		if ($this->mode & S_IFDIR) {
			return true;
		}

		return false;
	}

	function getPermissions(&$perm_number)
	{
		$val1 = $this->mode;
		$str = array(0 => "---", 1 => "x--", 2 => "-w-", 3 => "wx-", 4 => "--r", 5 => "x-r", 6 => "-wr", 7 => "xwr");
		$n = '-3';
		$m = 3;
		$perm = "";

		for ($i = 0; $i < 3; $i++) {
			$val2 = base_convert($val1, 10, 2);
			$val3 = substr($val2, $n, $m);
			$val4 = base_convert($val3, 2, 10);
			$perm_number = $val4 . $perm_number;

			foreach ($str as $key => $value) {
				if ($key == $val4) {
					$perm .= $value;
				}
			}

			$n += -3;
		}

		$perm = strrev($perm);

		return $perm;
	}

	function perDisplay($var)
	{
		if ($var === 'sizeper') {
			return array($this->getParentO()->size, $this->size, "");
		}
	}

	function display($var)
	{
		switch ($var) {

			case "sizeper":
				return $this->size;

			case "pvrename":
				return "rename";

			case "pvdownload":
				return "download";

			case "nname":
				if ($this->base === ".trash") {
					return "Trash";
				}

				if ($this->nname === '/') {
					return $this->getParentO()->nname;
				}

				if (isset($this->linkto)) {
					return "$this->base -> $this->linkto";
				}

				return $this->base;

				break;

			case "mode":
				if ($this->base === ".." || $this->base === ".trash") {
					return "";
				}
				
				return $this->getPermissions($dummy);
				
				break;

			case "realsize":
			case "size":
				if ($this->size < 1024) {
					return $this->size . 'B';
				}

				if ($this->size < 1024 * 1024) {
					return round($this->size / (1024), 0) . "K";
				}

				if ($this->size < 1024 * 1024 * 1024) {
					return round($this->size / (1024 * 1024), 1) . "M";
				}

				return round($this->size / (1024 * 1024 * 1024), 3) . "G";

			case "mtime":
				return lxgettime($this->mtime);

			default:
				return parent::display($var);

				break;
		}
	}

	function isInsideTrash()
	{
		if (basename(dirname($this->fullpath)) === ".trash") {
			return 1;
		}

		return 0;
	}

	function getSortTop($direction)
	{
		if ($direction === "r") {
			if ($this->ttype === "trash") {
				return "z";
			}
		}

		if ($this->ttype === "trash") {
			return "a";
		}

		if (!strncmp($this->ttype, "directory", 9)) {
			return "d";
		}

		return "e";
	}

	function setFileType()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$this->base = basename($this->nname);

		$this->fullpath = $this->getFullPath();

		$sel_append = NULL;

		if (isset($gbl->c_session->ssession_vars["frm_clip_action"]) && 
				$gbl->c_session->ssession_vars['frm_clip_root'] == $this->root && 
				$gbl->c_session->ssession_vars['frm_clip_server'] === $this->syncserver) {
			$selaction = $gbl->c_session->ssession_vars["frm_clip_action"];
			$sellist = $gbl->c_session->ssession_vars["frm_clip_list"];

			if (array_search_bool($this->nname, $sellist)) {
				$sel_append = "_" . $selaction;
			}

		}

		if ($this->base === "..") {
			$this->ttype = "back";
			return;
		}

		if ($this->base === ".trash") {
			$this->ttype = "trash";
			$this->protect = "na";
			return;
		}

		if ($this->isInsideTrash()) {
			$this->protect = "na";
		}
	/*
		if ($this->is_link()) {
			$this->ttype = "link";
			return;
		}
	*/

		$this->ttype = "{$this->ttype}$sel_append";

		if ($this->is_dir()) {
		//	if ($this->getParentO()->is__table('web')) {
			if ($this->getParentO()->getClass() === 'web') {
				if (strpos(dirname($this->nname), '/') === false || dirname($this->nname) === '/') {
					if ($this->base != "www") {
						$this->protect = "na";
					}
				}

				if (!isset($this->protect)) {
					try {
						$protname = $this->getParentO()->nname . "_" . coreFfile::getRealPath($this->nname);
						$prot = $this->getParentO()->getFromList("dirprotect", $protname);
						if ($prot->status != 'nonexistant') {
							$this->protect = "on";
						} else {
							$this->protect = 'off';
						}
					} catch (exception $e) {
						$this->protect = "off";
					}
				}
			}

			return;
		}

		$this->protect = "na";
	}

	function isExist($name)
	{
		if (lfile_exists($name)) {
			return true;
		}

		return false;
	}

	function getUploaddir()
	{
		return $this->fullpath;
	// 	return $this->root . "upload/";
	}

	function is_top()
	{
		if ($this->nname === "/") {
			return true;
		}

		return false;
	}

	function getCore()
	{
		list($core,) = explode("/", $this->nname, 2);

		return "{$this->root}$core";
	}

	static function initThisList($parent, $class)
	{
		$fpathp = $parent->fullpath;

		if (!$parent->is_dir()) {
			return null;
		}

		$duflag = $parent->duflag;

		$list = rl_exec_get($parent->__masterserver, $parent->__readserver, array("coreFfile", "get_full_stat"), 
			array($parent->__username_o, $parent->root, $fpathp, $duflag));

		foreach ((array)$list as $stat) {
			$file = basename($stat['name']);

			if ($file === "") {
				continue;
			}
			if ($file === ".") {
				continue;
			}

		//	$fpath = str_replace("//", "/", $fpathp . "/" . $file);
		//	$file = str_replace("//", "/", $parent->nname . "/" . $file);

			$fpath = preg_replace('/(\/){1,3}(.*)/', '/$2', "{$fpathp}/{$file}");
			$file = preg_replace('/(\/){1,3}(.*)/', '/$2', "{$parent->nname}/{$file}");
			
			if (!isset($parent->ffile_l)) {
				$parent->ffile_l = null;
			}

			$parent->ffile_l[$file] = new Ffile($parent->__masterserver, $parent->__readserver, $parent->root, $file, 
				$parent->__username_o);
			$parent->ffile_l[$file]->setFromArray($stat);
			$parent->ffile_l[$file]->__parent_o = $parent->getParentO();
			$parent->ffile_l[$file]->setFileType();

		}

		$__tv = null;

		return $__tv;
	}

	function get()
	{
		global $gbl, $sgbl, $login, $ghtml;

		static $st;

		if (isset($this->download_f) && $this->download_f) {
			$numlines = 'download';
		} else {
			if ($this->getParentO()->is__table('llog')) {
				$numlines = 20;
			} else {
				$numlines = null;
			}
		}

		if ($st > 0) {
?>
	Called more than once
<?php
		}
		$st++;

		$this->duflag = $gbl->getSessionV('ffile_duflag');
		$gbl->setSessionV('ffile_duflag', false);
		$this->numlines = $numlines;
		$stat = rl_exec_get($this->__masterserver, $this->__readserver, array("coreFfile", "getLxStat"), 
			array($this->__username_o, $this->root, $this->getFullPath(), $numlines, $this->duflag));

	//	dprintr($stat);

		if (!isset($this->readonly)) {
			$this->readonly = 'off';
		}

		$this->setFromArray($stat);

		if (!$this->isOn('readonly')) {
			$this->__flag_showheader = true;
		}

		$this->setFileType();
	}

	static function createListBlist($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$base = dirname($parent->nname);
		$top = $parent->root;

		if (!$parent->is_top()) {
		//	$blist[] = array("__ext|a=show&frm_o_o[nname]=$base|a=update&sa=go_home|", 1);
		//	$blist[] = array("__ext|a=show&frm_o_o[nname]=$top|a=update&sa=go_up|", 1);
		}

		$blist = null;
		
		if ($parent->isOn('readonly')) {
			return $blist;
		}

		$blist[] = array("a=update&sa=toggle_dot", 1);

		if ($parent->isOn('ostemplate')) {
			$blist[] = array("a=update&sa=filerealdelete", 0, NULL, 1);

			return $blist;
		}

		if ($parent->base === ".trash") {
			$blist[] = array("a=update&sa=restore_trash");
			$blist[] = array("a=update&sa=clear_trash");
		} else if ($parent->base === "__backup" || basename($parent->root) === '__backup') {
			$blist[] = array("a=update&sa=filerealdelete", 0, NULL, 1);
			$blist[] = array("a=update&sa=copy");
		} else if ($parent->isOn('browsebackup')) {
			$blist[] = array("a=update&sa=copy");
		} else {
			$blist[] = array("a=updateform&sa=newdir", 1);
			$blist[] = array("a=updateform&sa=newfile", 1);
			$blist[] = array("a=update&sa=copy");
			$blist[] = array("a=update&sa=cut");
			
			if ($gbl->isetSessionV("frm_clip_action")) {
				$inactive = NULL;
			} else {
				$inactive = "_inactive";
			}

			$blist[] = array("a=update&sa=paste$inactive", 1, $inactive);

			$blist[] = array("a=update&sa=filedelete");
			$blist[] = array("a=update&sa=filerealdelete", 0, NULL, 1);

			$blist[] = array("a=update&sa=zip_file");
		}

		return $blist;
	}

	static function createListAlist($parent, $class)
	{
		return null;
	}

	static function createSelectListNlist($parent)
	{
		$nlist['ttype'] = '2%';
		$nlist['nname'] = '80%';
		$nlist["size"] = "10%";
		$nlist["mtime"] = "10%";

		return $nlist;
	}

	static function createListNlist($parent, $view)
	{
		global $gbl, $sgbl, $login, $ghtml; 

		$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'ffile');

		$duflag = $parent->duflag;

		$nlist["ttype"] = "2%";

		if ($duflag) {
			$nlist["size"] = "10%";
			$nlist["sizeper"] = "10%";
		} else {
			$nlist["size"] = "10%";
		}

		$nlist["nname"] = "70%";

	//	if ($parent->getParentO()->is__table('web')) {
		if ($parent->getParentO()->getClass() === 'web') {
			$nlist["protect"] = "3%";
		}

		$nlist["mtime"] = "10%";

		$nlist["other_username"] = "10%";

		$nlist["mode"] = "10%";

		$nlist["pvrename"] = "2%";
		$nlist["pvdownload"] = "2%";

		return $nlist;
	}
}

