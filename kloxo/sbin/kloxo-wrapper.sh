#!/bin/sh

progname=kloxo

#source ../bin/common/function.sh
source ../sbin/function.sh

kill_and_save_pid wrapper;
wrapper_main;


