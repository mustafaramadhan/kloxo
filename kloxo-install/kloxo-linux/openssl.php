<?php
//
//    Kloxo, Hosting Panel
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2011     LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

include_once "htmllib/lib/include.php";

createNewcertificate();

function createNewcertificate()
{
    global $gbl, $login, $ghtml;
    $cerpath = "server.crt";
    $keypath = "server.key";
    $requestpath = "a.csr";

    $ltemp["countryName"] = "IN";
    $ltemp["stateOrProvinceName"] = "Bn";
    $ltemp["localityName"] = "Bn";
    $ltemp["organizationName"] = "LxCenter";
    $ltemp["organizationalUnitName"] = "Kloxo";
    $ltemp["commonName"] = "Kloxo";
    $ltemp["emailAddress"] = "contact@lxcenter.org";

    $privkey = openssl_pkey_new();
    openssl_pkey_export_to_file($privkey, $keypath);
    $csr = openssl_csr_new($ltemp, $privkey);
    openssl_csr_export_to_file($csr, $requestpath);

    $sscert = openssl_csr_sign($csr, null, $privkey, 365);
    openssl_x509_export_to_file($sscert, $cerpath);

    $src = getcwd();
    $dest = '/usr/local/lxlabs/kloxo/ext/lxhttpd/conf';
    root_execsys("lxfilesys_mkdir", $dest . "/ssl.crt/");
    root_execsys("lxfilesys_mkdir", $dest . "/ssl.key/");
    root_execsys("lxfilesys_mv", "$src/$cerpath", $dest . "/ssl.crt/" . $cerpath);
    root_execsys("lxfilesys_mv", "$src/$keypath", $dest . "/ssl.key/" . $cerpath);
    root_execsys("lxfilesys_mv", "$src/$requestpath", "$dest/$requestpath");

}
