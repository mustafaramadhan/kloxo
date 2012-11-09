#!/bin/sh

# release on Kloxo 6.2.0
# by mustafa.ramadhan@lxcenter.org

if [ "$1" == "--help" ] ; then
	echo
	echo " ---------------------------------------------------------------------------"
	echo "  format: sh $0 --with-repofile"
	echo " ---------------------------------------------------------------------------"
	echo "   --createrepo - also creating '/etc/yum.repos.d/kloxo-local.repo' file"
	echo
	echo " * Local repo inside /home/rpms and running this script will be"
	echo "      setup repodata inside /home/rpms"
	exit;
fi

echo
echo "- For help, type '$0 --help'"

if [ "$#" == 0 ] ; then
	echo "- No argument supplied. Defaulting to creating local-repo without .repo file"
fi

rpm --quiet -q createrepo -q

if [ $? != 0 ] ; then
	yum install createrepo -q -y
fi

mkdir -p /home/rpms/release/centos5/{i386,i686,x86_64,noarch,SRPMS}
mkdir -p /home/rpms/release/centos6/{i386,i686,x86_64,noarch,SRPMS}

mkdir -p /home/rpms/testing/centos5/{i386,i686,x86_64,noarch,SRPMS}
mkdir -p /home/rpms/testing/centos6/{i386,i686,x86_64,noarch,SRPMS}

chmod -R o-w+r /home/rpms

list=(i386 i686 x86_64 noarch SRPMS)

for item in ${list[*]}
do
	createrepo -q /home/rpms/release/centos5/$item
	createrepo -q /home/rpms/release/centos6/$item
done

if [ "$1" == "--with-repofile" ] ; then

RELEASEVER=$(rpm -q --qf "%{VERSION}" $(rpm -q --whatprovides redhat-release))

echo "- Creating local-repo inside '/home/rpms' "
echo "     with kloxo-local.repo inside '/etc/yum.repos.d'"

echo "[kloxo-release-local-noarch]
name=kloxo-release-local-noarch
baseurl=file:///home/rpms/release/centos$RELEASEVER/noarch/
enabled=1
gpgcheck=0

[kloxo-release-local-arch]
name=kloxo-release-local-\$basearch
baseurl=file:///home/rpms/release/centos$RELEASEVER/\$basearch/
enabled=1
gpgcheck=0

[kloxo-testing-local-noarch]
name=kloxo-release-local-noarch
baseurl=file:///home/rpms/testing/centos$RELEASEVER/noarch/
enabled=0
gpgcheck=0

[kloxo-testing-local-arch]
name=kloxo-release-local-\$basearch
baseurl=file:///home/rpms/testing/centos$RELEASEVER/\$basearch/
enabled=0
gpgcheck=0" > /etc/yum.repos.d/kloxo-local.repo

fi

echo
