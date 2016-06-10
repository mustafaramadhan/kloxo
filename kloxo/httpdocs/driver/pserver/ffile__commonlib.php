<?php

class ffile__common
{
	static function clearFromTrash($root, $name)
	{
		if (basename($name)) {
			lxfile_rm("$root/.trash/.__trash_" . basename($name));
			lxfile_rm_rec("$root/.trash/" . basename($name));
		}
	}

	static function restoreFromTrash($user, $root, $name)
	{
		$trashcont = "$root/.trash/.__trash_" . basename($name);
		$dst = trim(lfile_get_contents($trashcont));

	//	new_process_mv_rec($user, "$root/.trash/" . basename($name), "$root/$dst");
		new_process_mv_rec('__system__', "$root/.trash/" . basename($name), "$root/$dst");

		lxfile_rm("$root/.trash/.__trash_" . basename($name));
	}


	static function moveToTrash($user, $root, $name)
	{
		$trashcont = "$root/.trash/.__trash_" . basename($name);
		$fullpath = "$root/$name";

		lxfile_rm_rec("$root/.trash/" . basename($name));
		lxfile_rm_rec($trashcont);
		$res = lxuser_mkdir($user, "$root/.trash/");
		lfile_put_contents($trashcont, $name . "\n");

	//	$res = new_process_mv_rec($user, $fullpath, "$root/.trash");
		$res = new_process_mv_rec('__system__', $fullpath, "$root/.trash");

	//	unlink($f);
	}

	function moveAllToTrash()
	{
		foreach ($this->main->filedelete_list as $f) {
			self::moveToTrash($this->main->__username_o, $this->main->root, "$f");
		}
	}

	function fileRealDelete()
	{
		foreach ($this->main->filerealdelete_list as $f) {
			if (!$f) {
				continue;
			}
			
			$fullname = "{$this->main->root}/$f";
			lxfile_rm_rec("$fullname");
		}

		return $this->main->root;
	}

	function resampimagejpg($forcedwidth, $forcedheight, $sourcefile, $destfile)
	{
		$fw = $forcedwidth;
		$fh = $forcedheight;
		$is = getimagesize($sourcefile);
		
		if ($is[0] >= $is[1]) {
			$orientation = 0;
		} else {
			$orientation = 1;
			$fw = $forcedheight;
			$fh = $forcedwidth;
		}
		
		if ($is[0] > $fw || $is[1] > $fh) {
			if (($is[0] - $fw) >= ($is[1] - $fh)) {
				$iw = $fw;
				$ih = ($fw / $is[0]) * $is[1];
			} else {
				$ih = $fh;
				$iw = ($ih / $is[1]) * $is[0];
			}
			
			$t = 1;
		} else {
			$iw = $is[0];
			$ih = $is[1];
			$t = 2;
		}

		if ($t == 1) {
			$img_src = imagecreatefromgif($sourcefile);
			$img_dst = imagecreatetruecolor($iw, $ih);
			imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $iw, $ih, $is[0], $is[1]);
			
			if (!imagegif($img_dst, $destfile, 90)) {
				return;
			}
		} else if ($t == 2) {
			copy("$sourcefile", "$destfile");
		}
	}

	function throw_if_not_magick()
	{
		global $login;

		$ret = lxshell_return("rpm", "-q", "ImageMagick");
		
		if ($ret) {
			throw new lxException($login->getThrow('no_imagemagick'));
		}
	}

	function resizeImage()
	{
		$this->throw_if_not_magick();

		$oldimage = coreFfile::getRealPath($this->main->old_image_name_f);
		
		if ($this->main->isOn('copy_old_image_flag_f')) {
			lxfile_cp($this->main->getFullPath(), "{$this->main->root}/$oldimage");
		}
		
		$tfile = lx_tmp_file("resizeimage");
		lxfile_rm($tfile);
		$fp = $this->main->getFullPath();
		$fp = expand_real_root($fp);
		$ext = coreFfile::getExtension($fp);
		$tfile .= ".$ext";

		$geom = "{$this->main->image_width}x{$this->main->image_height}";
		lxfile_cp($this->main->getFullPath(), "$tfile");
		lxuser_return($this->main->__username_o, "convert", "-scale", $geom, "$tfile", $this->main->getFullPath());
	//	lxshell_return("convert", "-scale", $geom, "$tfile", $this->main->getFullPath());
		lxfile_rm("$tfile");
	}

	function createThumbnail()
	{
		$this->throw_if_not_magick();

		$dir = $this->main->getFullPath();
		$list = lscandir_without_dot($dir);
		lxfile_mkdir("$dir/thumbs");
		$geom = "{$this->main->image_width}x{$this->main->image_height}";
		dprintr($list);
		
		foreach ($list as $l) {
			if (!coreFfile::is_image($l)) {
				continue;
			}
			
			$newf = "$dir/thumbs/th_$l";
			lxuser_return($this->main->__username_o, "convert", "-scale", $geom, "$dir/$l", "$newf");
		//	lxshell_return("convert", "-scale", $geom, "$dir/$l", "$newf");
		}
	}

	function convertImage()
	{
		$this->throw_if_not_magick();

		$fp = $this->main->getFullPath();
		$file = coreFfile::getWithoutExtension($fp);
		$newfile = "$file.{$this->main->new_format_f}";
		lxuser_return($this->main->__username_o, "convert", $fp, "$newfile");
	//	lxshell_return("convert", $fp, $newfile);
	}

	function restoreTrash()
	{
		foreach ($this->main->restore_trash_list as $f) {
			ffile__common::restoreFromTrash($this->main->__username_o, $this->main->root, "$f");
		}
	}

	function clearTrash()
	{
		foreach ($this->main->clear_trash_list as $f) {
			ffile__common::clearFromTrash($this->main->root, "$f");
		}
	}

	function downloadFromHttp()
	{
		global $login;

		$file = basename($this->main->download_url_f);
		
		if (!$file) {
			throw new lxException($login->getThrow('please_type_full_url_including_file_name'), '', $file);
		}
		
		$fullpath = $this->main->getFullPath() . "/" . $file;
		check_file_if_owned_by_and_throw($fullpath, $this->main->__username_o);
		
		if (lxfile_exists($fullpath) && !$this->main->isOn('download_overwrite_f')) {
			throw new lxException($login->getThrow('file_exists'), '', $fullpath);
		}
		
		download_file($this->main->download_url_f, $fullpath);
		
		return $fullpath;
	}

	function downloadFromFtp()
	{
		global $login;

		$file = basename($this->main->download_ftp_file_f);
		
		if (!$file) {
			throw new lxException($login->getThrow('please_type_full_url_including_file_name'), '', $file);
		}
		
		$fullpath = $this->main->getFullPath() . "/" . $file;
		check_file_if_owned_by_and_throw($fullpath, $this->main->__username_o);
		
		if (lxfile_exists($fullpath) && !$this->main->isOn('download_overwrite_f')) {
			throw new lxException($login->getThrow('file_exists'), '', $fullpath);
		}
		
		download_from_ftp($this->main->download_ftp_f, $this->main->download_username_f, 
			$this->main->download_password_f, $this->main->download_ftp_file_f, $fullpath);

		return $fullpath;
	}

	function zipExtract()
	{
		global $login;

		$extractdir = coreFfile::getRealPath($this->main->zip_extract_dir_f);
		$fulzippath = "{$this->main->root}/$extractdir";

		// MR -- file automatically with .tar extension if enabled
		$extracttotar = $this->main->extract_to_tar_f;

		$zipd = trim($this->main->zip_extract_dir_f);
		$zipd = trim($this->main->zip_extract_dir_f, "/");

		if (!$zipd) {
			// MR -- no reason not able unzip in root because root mean docroot
		//	throw new lxException($login->getThrow('can_not_unzip_in_root'), '', $this->main->root);
		}

		if (lxfile_exists($fulzippath) && $this->main->__username_o === 'root') {
		//	throw new lxexception($login->getThrow("root_can_not_extract_to_existing_dir"), '', $fulzippath);
		}

		lxfile_mkdir($fulzippath);
		lxfile_unix_chown($fulzippath, $this->main->__username_o);

		$dir = expand_real_root($fulzippath);
		$file = expand_real_root($this->main->getFullPath());

		if ($file[0] !== '/') {
			$fullpath = getcwd() . "/$file";
		} else {
			$fullpath = $file;
		}

		$fullpath = expand_real_root($fullpath);

		if ($this->main->ttype === "zip") {
			$cmd = "/usr/bin/unzip -oq '$fullpath'";
		} else if ($this->main->ttype === "tgz") {
			if ($extracttotar) {
				$cmd = "/bin/gzip -dfN '$fullpath'";
			} else {
				$cmd = "/bin/tar -xzf '$fullpath'";
			}
		} else if ($this->main->ttype === "tbz2") {
			if ($extracttotar) {
				$cmd = "/usr/bin/bzip2 -dfk '$fullpath'";
			} else {
				$cmd = "/bin/tar -xjf '$fullpath'";
			}
		} else if ($this->main->ttype === "gz") {
			$cmd = "/bin/gzip -dfN '$fullpath'";
		} else if ($this->main->ttype === "bz2") {
			$cmd = "/usr/bin/bzip2 -dfk '$fullpath'";
		} else if ($this->main->ttype === "txz") {
			if ($extracttotar) {
				$cmd = "/usr/bin/xz -dfk '$fullpath'";
			} else {
				$cmd = "/bin/tar -xJf '$fullpath'";
			}
		} else if ($this->main->ttype === "xz") {
			$cmd = "/usr/bin/xz -dfk '$fullpath'";
		} else if ($this->main->ttype === "p7z") {
			$cmd = "/usr/bin/7za e -y '$fullpath'";
		} else if ($this->main->ttype === "rar") {
			$cmd = "/usr/bin/unrar e -y '$fullpath'";
		} else {
			// MR -- as .tar
			$cmd = "/bin/tar -xf '$fullpath'";
		}

		new_process_cmd($this->main->__username_o, $dir, $cmd);
	//	new_process_cmd('__system__', $dir, $cmd);

		return $dir;
	}


	function zipFile()
	{
		global $login;

		foreach ($this->main->zip_file_list as &$_t_f) {
			$_t_f = coreFfile::removeLeadingSlash($_t_f);
			$_t_f = basename($_t_f);
			$_t_f = "\"$_t_f\"";
		}
		
		$list = implode(" ", $this->main->zip_file_list);
		$oldir = getcwd();
		$fullpath = expand_real_root($this->main->fullpath);
	/*
		$fz = $fullpath . "/" . $this->main->zip_file_f;
	
		if (lxfile_exists($fz)) {
			throw new lxException($login->getThrow('file_exists'), '', $fullpath);
		}
	*/
	//	$date = date("M-d-H");
		$date = date("Y-m-d-h-i-s");
	//	check_file_if_owned_by_and_throw("NewArchive_$date.zip", $this->main->__username_o);

	//	$ret = new_process_cmd($this->main->__username_o, $fullpath, "zip -qu -r NewArchive_$date $list");
		$ret = new_process_cmd('__system__', $fullpath, "zip -qu -r NewArchive_$date $list");

		return "$fullpath/NewArchive_$date.zip";
	}

	function newDir()
	{
		global $login;

		$rpath = $this->main->fullpath;
		$name = $this->main->newfolder_f;
		$path = "$rpath/$name";
		
		if (lxfile_exists($path)) {
			throw new lxException($login->getThrow('file_exists'), '', $path);
		}
		
		lxfile_mkdir($path);
		
		return $path;
	}

	function filePaste()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		if ($this->main->paste_list) {
			// Hack... Specifically checking for pasteaction. Should just use it directly in the command.
			$arglist[] = $this->main->__username_o;
			
			foreach ($this->main->paste_list as &$_tl) {
				$_tl = "{$this->main->pasteroot}/$_tl";
			}
			
		//	$arglist = array_merge($arglist, $this->main->paste_list);
			$arglist[] = $this->main->paste_list;
			$arglist[] = $this->main->fullpath;

			if ($this->main->syncserver !== $this->main->pasteserver) {
				foreach ($this->main->paste_list as $p) {
					$this->getFromRemote($p);
				}
			} else {
				if ($this->main->pasteaction === 'copy') {
					foreach ($this->main->paste_list as $p) {
					//	new_process_cp_rec($this->main->__username_o, $p, $this->main->fullpath);
						new_process_cp_rec('__system__', $p, $this->main->fullpath);
					}
				} else {
					foreach ($this->main->paste_list as $p) {
					//	new_process_mv_rec($this->main->__username_o, $p, $this->main->fullpath);
						new_process_mv_rec('__system__', $p, $this->main->fullpath);
					}
				}
			}

			if ($sgbl->isKloxo()) {
				$pp = basename($p);
			//	lxfile_unix_chown_rec("{$this->main->fullpath}/$pp", $this->main->__username_o);
			}
		}
	}

	function getFromRemote($p)
	{
		$filepass = $this->main->filepass;
		getFromRemote($this->main->pasteserver_realip, $filepass[$p], $this->main->fullpath, $p);
		
		return;

		$bp = basename($p);
		
		if ($filepass[$p]['type'] === 'dir') {
			$tfile = lx_tmp_file("__path_tmp", "lx_$bp");
			getFromFileserv($this->main->pasteserver, $this->main->filepass[$p], $tfile);
			lxfile_mkdir("{$this->main->fullpath}/$bp");
			lxshell_unzip("__system__", "{$this->main->fullpath}/$bp", $tfile);
			lunlink($tfile);
		} else {
			getFromFileserv($this->main->pasteserver, $this->main->filepass[$p], "{$this->main->fullpath}/$bp");
		}
	}

	function uploadDirect()
	{
		global $login;

		$filename = "{$this->main->getFullPath()}/{$this->main->upload_file_name}";
		check_file_if_owned_by_and_throw($filename, $this->main->__username_o);
		
		dprintr($this->main->upload_overwrite_f);
		
		if (lfile_exists($filename)) {
			if (!$this->main->isOn('upload_overwrite_f')) {
				throw new lxException($login->getThrow('file_upload_already_exists'), '', $filename);
			} else {
				lxfile_rm("$filename");
			}
		}
		
		dprintr($this->main);
		
		getFromFileserv($this->main->__var_upload_tmp_server, $this->main->__var_upload_filepass, "$filename");
		
		return $filename;
	}

	function reName()
	{
		global $login;

		$directory = dirname($this->main->fullpath);
		$new = $directory . "/" . $this->main->newname;
		
		if (lfile_exists($new)) {
			throw new lxException($login->getThrow('file_rename_already_exists'), '', $new);
		}
		
	//	new_process_mv_rec($this->main->__username_o, $this->main->fullpath, "$new");
		new_process_mv_rec('__system__', $this->main->fullpath, "$new");
	}
}
