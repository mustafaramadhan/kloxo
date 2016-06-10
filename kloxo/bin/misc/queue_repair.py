#!/usr/bin/python
'''queue_repair.py - qmail tools in Python.
Copyright (C) 2001 Charles Cazabon <pqt @ discworld.dyndns.org>

This program is free software; you can redistribute it and/or
modify it under the terms of version 2 of the GNU General Public License
as published by the Free Software Foundation.  A copy of this license should
be included in the file COPYING.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

'''

__version__ =   '0.9.0'
__author__ =    'Charles Cazabon <pqt @ discworld.dyndns.org>'

import sys
import os
from stat import *
import string
import pwd
import grp
import getopt


#######################################
# globals
#######################################

confqmail    = '/var/qmail'
wd                  = None
testmode            = 1
checked_dir         = {}
checked_owner       = {}
checked_mode        = {}


#######################################
# data
#######################################

users = {
    'alias' : None,
    'qmaild' : None,
    'qmaill' : None,
    'qmailp' : None,
    'qmailq' : None,
    'qmailr' : None,
    'qmails' : None,
}

groups = {
    'qmail' : None,
    'nofiles' : None,
}

dirs = {
    # Directories to check; format is:
    #   key: pathname - all paths are relative to conf-qmail
    #   data: (user, group, mode, split)
    #       split is:  0 : no, 1 : yes, -1 : only with big-todo
    'queue' :           ('qmailq', 'qmail', 0750, 0),
    'queue/bounce' :    ('qmails', 'qmail', 0700, 0),
    'queue/info' :      ('qmails', 'qmail', 0700, 1),
    'queue/intd' :      ('qmailq', 'qmail', 0700, -1),
    'queue/local' :     ('qmails', 'qmail', 0700, 1),
    'queue/lock' :      ('qmailq', 'qmail', 0750, 0),
    'queue/mess' :      ('qmailq', 'qmail', 0750, 1),
    'queue/pid' :       ('qmailq', 'qmail', 0700, 0),
    'queue/remote' :    ('qmails', 'qmail', 0700, 1),
    'queue/todo' :      ('qmailq', 'qmail', 0750, -1),
}

nondirs = {
    # Files to check; format is:
    #   key: pathname - all paths are relative to conf-qmail
    #   data: (user, group, mode)
    'queue/lock/sendmutex' :    ('qmails', 'qmail', 0600),
    'queue/lock/tcpto' :        ('qmailr', 'qmail', 0644),
}


#######################################
# functions
#######################################

#######################################
def primes(min, max):
    '''primes(min, max)

    Return a list of primes between min and max inclusive.
    '''
    result = []
    primelist = [2]
    if min <= 2:
        result.append(2)
    i = 3
    while i <= max:
        for p in primelist:
            if (i % p == 0) or (p * p > i): break
        if (i % p <> 0):
            primelist.append(i)
            if i >= min:
                result.append(i)
        i = i + 2
    return result

#######################################
def err(s, showhelp=0):
    '''err(s, showhelp=0)

    Write s + '\n' to stderr, optionally call show_help(), and exit.
    '''
    sys.stderr.write('%s\n' % s)
    if showhelp:
        show_help()
    if wd:
        os.chdir(wd)
    sys.exit(1)

#######################################
def msg(s):
    '''msg(s)

    Write s + '\n' to stdout.
    '''
    sys.stdout.write('%s\n' % s)

#######################################
def is_splitdir(is_split, bigtodo):
    '''is_splitdir(is_split, bigtodo)

    Return 0 if directory should not contain split subdirectories, 1 if it
    should.
    '''
    return (is_split == 1) or (is_split == -1 and bigtodo)

#######################################
def determine_users():
    '''determine_users()

    Look up UIDs and GIDs for all keys in globals users and groups which are
    not already set.
    '''
    global users, groups
    msg('finding qmail UIDs/GIDs...')
    us = users.keys()
    gs = groups.keys()
    for u in us:
        if users[u]:
            # Handle case of someone else determining UIDs for us
            msg('  %-7s preset as UID %i' % (u, users[u]))
            continue
        try:
            users[u] = pwd.getpwnam(u)[2]
        except KeyError:
            err('no uid for %s' % u)
        msg('  %-7s : UID %i' % (u, users[u]))
    for g in gs:
        if groups[g]:
            # Handle case of someone else determining GIDs for us
            msg('  %-7s preset as GID %i' % (g, groups[g]))
            continue
        try:
            groups[g] = grp.getgrnam(g)[2]
        except KeyError:
            err('no gid for %s' % g)
        msg('  %-7s : GID %i' % (g, groups[g]))

#######################################
def check_dir(path, user, group, mode):
    '''check_dir(path, user, group, mode)

    Verify path is an existing directory, that it owned by user:group, and
    that it has octal mode mode.  If testmode is set, create path if it
    doesn't exist.
    '''
    if checked_dir.has_key(path):
        return
    msg('  checking directory %s...' % path)
    if not os.path.exists(path):
        msg('  directory %s does not exist' % path)
        if not testmode:
            os.makedirs(path, mode)
    else:
        if os.path.islink(path):
            msg('  %s is a symlink instead of directory' % path)
            if not testmode:
                os.unlink(path)
        if not os.path.isdir(path):
            msg('  %s is not a directory' % path)
            if not testmode:
                os.unlink(path)
    chown(path, user, group)
    chmod(path, mode)
    checked_dir[path] = None

#######################################
def chown(path, user, group):
    '''chown(path, user, group)

    Verify path is owned by user:group, and make it so if testmode is not set.
    '''
    if checked_owner.has_key(path):
        return
    uid = users[user]
    gid = groups[group]
    try:
        s = os.stat(path)
        if s[ST_UID] != uid or s[ST_GID] != gid:
            msg('  %s ownership %i:%i, should be %s:%s' % (path,
                s[ST_UID], s[ST_GID], user, group))
            if not testmode:
                os.chown(path, uid, gid)
                s = os.stat(path)
                msg('  fixed, %s ownership %i:%i' % (path, s[ST_UID], s[ST_GID]))
            else:
                msg('  testmode, not fixing')
    except OSError, o:
        err(o or '[no error message]')
    checked_owner[path] = None

#######################################
def chmod(path, mode):
    '''chmod(path, mode)

    Verify path has mode mode, and make it so if testmode is not set.
    '''
    if checked_mode.has_key(path):
        return
    try:
        s = os.stat(path)
        curmode = S_IMODE(s[ST_MODE])
        if curmode != mode:
            msg('  %s is mode %o, should be %o' % (path, curmode, mode))
            if not testmode:
                os.chmod(path, mode)
                s = os.stat(path)
                newmode = S_IMODE(s[ST_MODE])
                msg('  changed %s mode to %o' % (path, newmode))
            else:
                msg('  testmode, not fixing')
    except OSError, o:
        err(o or '[no error message]')
    checked_mode[path] = None

#######################################
def determine_split():
    '''determine_split()

    Return probable conf-split value of queue based on contents.
    '''
    splits = []
    msg('determining conf-split...')
    for (path, (user, group, mode, is_split)) in dirs.items():
        if is_split != 1:
            continue
        highest = 0
        contents = os.listdir(path)
        for item in contents:
            p = os.path.join(path, item)
            if os.path.islink(p):
                msg('  found unexpected symlink %s' % p)
                continue
            if not os.path.isdir(p):
                msg('  found unexpected non-directory %s' % p)
                continue
            try:
                i = int(item)
            except ValueError:
                msg('  found unexpected directory %s' % p)
                continue
            if i > highest:
                highest = i
        splits.append(highest)
    split = splits[0]
    for i in splits[1:]:
        if i != split:
            err('  not all subdirectories split the same; use --split N to force')
    # First split directory is '0'
    split = split + 1
    msg('  conf-split appears to be %i' % split)
    return split

#######################################
def determine_bigtodo(split):
    '''determine_bigtodo(split)

    Return 1 if big-todo appears to be in use based on contents of queue,
    0 otherwise.
    '''
    splits = []
    bigtodo = 0
    msg('determining big-todo...')
    for i in range(split):
        p = os.path.join('queue/todo', str(i))
        if os.path.islink(p):
            msg('  found unexpected symlink %s' % p)
        elif os.path.isdir(p):
            splits.append(i)
        elif not os.path.exists(p):
            # big-todo probably not in use
            pass
        else:
            msg('  found unexpected direntry %s' % p)

    if splits == range(split):
        # big-todo apparently in use
        bigtodo = 1
        msg('  big-todo found')
    elif splits:
        # big-todo in use, but doesn't match split
        err('  todo split != split; if using --split N, use --bigtodo to force')
    else:
        msg('  big-todo not found')

    return bigtodo

#######################################
def check_dirs(paths, split, bigtodo):
    '''check_dirs(paths, split, bigtodo)

    Verify ownership, mode, and contents of each queue directory in paths.
    '''
    msg('checking main queue directories...')
    _dirs = paths.keys()
    _dirs.sort()
    for path in _dirs:
        (user, group, mode, is_split) = paths[path]
        check_dir(path, user, group, mode)

    msg('checking split sub-directories...')
    for (path, (user, group, mode, is_split)) in paths.items():
        if path in ('queue', 'queue/lock'):
            # Nothing in these directories to check at this point
            continue
        this_split = is_splitdir(is_split, bigtodo)
        if not this_split:
            splits = []
        else:
            splits = range(split)
            for i in splits:
                splitpath = os.path.join(path, str(i))
                check_dir(splitpath, user, group, mode)

        try:
            contents = os.listdir(path)
        except OSError:
            # Directory missing
            if testmode:
                continue
            err('bug -- directory %s missing, should exist by now' % path)

        for item in contents:
            p = os.path.join(path, item)
            if this_split:
                if (is_split == -1) and os.path.isfile(p):
                    # Found possible file in path while converting queue to
                    # big-todo
                    try:
                        i = int(item)
                        if not testmode:
                            # Move to '0' split subdirectory; will be
                            # fixed later by check_hash_and_ownership
                            new_p = os.path.join(path, '0', item)
                            msg('  moving %s to %s' % (p, new_p))
                            os.rename(p, new_p)
                    except ValueError:
                        # Not a message file
                        msg('  found unexpected file %s' % p)
                        continue
                # This directory should contain only split subdirectories
                if not os.path.isdir(p):
                    msg('  found unexpected direntry %s' % p)
                    continue
                try:
                    i = int(item)
                    if i not in splits:
                        msg('  found unexpected split subdirectory %s' % p)
                        if not testmode:
                            files = os.listdir(p)
                            for f in files:
                                # Move any files in this to-be-remove split subdir
                                # into the 0 splitdir.  Will be moved into the
                                # proper split subdir later by
                                # check_hash_and_ownership().
                                filep = os.path.join(p, f)
                                msg('  preserving file %s' % filep)
                                os.rename(filep, os.path.join(path, '0', f))
                            os.removedirs(p)
                except ValueError:
                    msg('  found unexpected direntry %s' % p)
            else:
                # This directory should contain only files
                if os.path.isdir(p):
                    msg('  found unexpected directory %s' % p)
                    try:
                        i = int(item)
                    except ValueError:
                        msg('  %s not a split subdirectory; ignoring' % p)
                        continue
                    msg('  %s is a split subdirectory; %s should not be split' % (p, path))
                    if not testmode:
                        savefiles = os.listdir(p)
                        if savefiles:
                            msg('  moving files from %s to %s' % (p, path))
                            for f in savefiles:
                                os.rename(os.path.join(p, f), os.path.join(path, f))
                        os.rmdir(p)
                elif not os.path.isfile(p):
                    msg('  found unexpected direntry %s; ignoring' % p)
                    continue
                else:
                    # Found file
                    pass

#######################################
def check_files(paths):
    '''check_files(paths)

    Verify ownership and mode of each queue file in paths.
    '''
    msg('checking files...')
    for (path, (user, group, mode)) in paths.items():
        if os.path.exists(path):
            if not os.path.isfile(path):
                msg('  %s is not a file' % path)
                if not testmode:
                    os.unlink(path)
        else:
            msg('  file %s does not exist' % path)
        if not os.path.exists(path) and not testmode:
            open(path, 'w')
        chown(path, user, group)
        chmod(path, mode)

#######################################
def check_trigger():
    '''check_trigger()

    Verify ownership, mode, and inode type of trigger fifo.
    '''
    path = 'queue/lock/trigger'
    user = 'qmails'
    group = 'qmail'

    if not os.path.exists(path):
        msg('  %s missing' % path)
    else:
        if os.path.islink(path):
            msg('  %s is a symlink instead of fifo' % path)
            if not testmode:
                os.unlink(path)
        else:
            mode = os.stat(path)[ST_MODE]
            if not S_ISFIFO(mode):
                msg('  %s not a fifo' % path)
                if not testmode:
                    os.unlink(path)
    if not os.path.exists(path) and not testmode:
        os.mkfifo(path)
    chown(path, user, group)
    chmod(path, 0622)

#######################################
def check_messages(path, split):
    '''check_messages(path, split)

    Return list of files found under path which are not named after their
    inode number.
    '''
    misnamed = []
    msg('checking queue/mess files...')
    for i in range(split):
        messdir = os.path.join(path, str(i))
        try:
            contents = os.listdir(messdir)
        except OSError:
            continue
        for f in contents:
            p = os.path.join(messdir, f)
            if os.path.islink(p):
                msg('  found unexpected symlink %s' % p)
                continue
            elif not os.path.isfile(p):
                msg('  found unexpected non-file %s' % p)
                continue
            try:
                filenum = int(f)
            except ValueError:
                msg('  found unexpected file %s' % p)
                continue

            s = os.stat(p)
            inode = s[ST_INO]
            if filenum == inode:
                continue
            # Found mess file not named after inode
            msg('  %s is inode %i' % (p, inode))
            # Will be fixed by fix_inode_names()
            misnamed.append((i, filenum, inode))
    return misnamed

#######################################
def fix_inode_names(paths, split, bigtodo, misnamed):
    '''fix_inode_names(paths, split, bigtodo, misnamed)

    For each path in paths, correct file names based on results of
    check_messages().  Correct split sub-directory location as well.
    '''
    msg('fixing misnamed messages...')
    for (path, (user, junk, junk, is_split)) in paths.items():
        for (oldhash, oldno, newno) in misnamed:
            if not is_splitdir(is_split, bigtodo):
                old_p = os.path.join(path, str(oldno))
                new_p = os.path.join(path, str(newno))
            else:
                old_p = os.path.join(path, str(oldhash), str(oldno))
                new_p = os.path.join(path, str(newno % split), str(newno))
            if os.path.exists(old_p):
                if os.path.islink(old_p):
                    msg('  found unexpected symlink %s' % old_p)
                    continue
                if not os.path.isfile(old_p):
                    msg('  found unexpected direntry %s' % old_p)
                    continue
                msg('  %s should be %s' % (old_p, new_p))
                if not testmode:
                    os.rename(old_p, new_p)
                    msg('    fixed')

#######################################
def check_hash_and_ownership(paths, split, bigtodo):
    '''check_hash_and_ownership(paths, split, bigtodo)

    For each path in paths, correct file ownership, mode, and split subdirectory
    of all files found.
    '''
    msg('checking split locations...')
    for (path, (user, group, junk, is_split)) in paths.items():
        if path in ('queue', 'queue/lock'):
            # Nothing in these directories to check at this point
            continue
        elif path in ('queue/mess', 'queue/todo'):
            mode = 0644
        else:
            mode = 0600
        this_split = is_splitdir(is_split, bigtodo)
        if this_split:
            splits = range(split)
        else:
            splits = ['']
        for splitval in splits:
            _dir = os.path.join(path, str(splitval))
            try:
                contents = os.listdir(_dir)
            except OSError:
                if not testmode:
                    err('bug -- directory %s missing, should exist by now' % _dir)
                continue
            for f in contents:
                old_p = os.path.join(_dir, f)
                try:
                    if not os.path.isfile(old_p):
                        raise ValueError
                    j = int(f)
                except ValueError:
                    msg('  found unexpected direntry %s; ignoring' % old_p)
                    continue
                # Check ownership and mode
                chown(old_p, user, group)
                chmod(old_p, mode)
                if not this_split:
                    continue
                # Check whether file is in correct split sub-directory
                hashv = j % split
                if hashv != splitval:
                    # message in wrong split dir
                    new_p = os.path.join(path, str(hashv), f)
                    msg('  %s should be %s' % (old_p, new_p))
                    if not testmode:
                        os.rename(old_p, new_p)
                        # Ensure ownership and mode
                        chown(new_p, user, group)
                        chmod(new_p, mode)
                        msg('    fixed')

#######################################
def get_current_messages(split):
    '''get_current_messages(split)

    Return list of all message files under queue/mess.
    '''
    messages = []
    msg('finding current messages...')
    for i in range(split):
        path = os.path.join('queue/mess', str(i))
        try:
            contents = os.listdir(path)
        except OSError:
            continue
        for item in contents:
            try:
                messages.append(int(item))
            except ValueError:
                if testmode:
                    pass
                else:
                    msg('  found unexpected direntry %s' % os.path.join(path, item))
    messages.sort()
    msg('  found %i messages' % len(messages))
    return messages

#######################################
def check_queue(qmaildir=confqmail, test=1, force_split=None, force_bigtodo=None, force_create=0, mathishard=0):
    '''check_queue(qmaildir=confqmail, test=1, force_split=None, force_bigtodo=None, force_create=0, mathishard=0)

    Verify (and correct if test is not set) queue structure rooted at
    qmaildir/queue.
    Determine conf-split automatically if force_split is not set.
    Determine if big-todo is in use automatically if force_bigtodo is not set.
    '''
    global wd
    global testmode
    testmode = test
    split = None

    wd = os.getcwd()
    try:
        os.chdir(qmaildir)
    except StandardError:
        err('failed to chdir to %s' % qmaildir)

    if testmode:
        msg('running in test-only mode')
    else:
        msg('running in repair mode')

    determine_users()

    if not force_split:
        try:
            split = determine_split()
        except OSError:
            msg('basic queue directories not found at %s' % qmaildir)

    if not split:
        if not force_create:
                err('  use --create to force creation of queue at %s' % qmaildir)
        # --create implies --repair
        testmode = 0
        if not force_split:
                err('if creating a new queue, you must supply a conf-split value with --split')
        split = int(force_split)
        if split < 1:
            err('split must be >= 1')
        if not force_bigtodo:
                err('if creating a new queue, you must supply either --bigtodo or --no-bigtodo')
        msg('using forced conf-split of %i' % split)
        msg('creating new queue at %s' % qmaildir)

    l = int(split * 0.8)
    h = int(split * 1.2)
    suggested_splits = primes(l, h)
    if not split in suggested_splits:
        msg('split should be prime, not %i:  suggestions %s' % (split, suggested_splits))
        if not mathishard and not testmode:
            err('  use --i-want-a-broken-conf-split to force non-prime split')

    if force_bigtodo == 1:
        bigtodo = 1
        msg('using forced big-todo')
    elif force_bigtodo == -1:
        bigtodo = 0
        msg('using forced non-big-todo')
    else:
        bigtodo = determine_bigtodo(split)

    check_dirs(dirs, split, bigtodo)
    check_files(nondirs)
    check_trigger()

    misnamed = check_messages('queue/mess', split)

    # Handle misnamed files in directories
    if misnamed:
        fix_inode_names(dirs, split, bigtodo, misnamed)

    # Handle mis-hashed files and bad owner/group/mode
    check_hash_and_ownership(dirs, split, bigtodo)

#######################################
def show_help():
    '''show_help()

    Display usage information.
    '''
    msg('\n'
        'Usage:  queue_repair.py [options] [conf-qmail]\n')
    msg('Options:\n'
        '  conf-qmail                    (default:  %s)' % confqmail)
    msg(
        '  -t   or --test                Test only; do not modify the filesystem\n'
        '  -r   or --repair              Repair errors found        (default: test)\n'
        '  -b   or --bigtodo             Force use of big-todo      (default: auto)\n'
        '  -n   or --no-bigtodo          Force non-use of big-todo  (default: auto)\n'
        '  -s N or --split N             Force conf-split of N      (default: auto)\n'
        '  -c   or --create              Force creation of queue    (default: no)\n'
        '  --i-want-a-broken-conf-split  Force non-prime conf-split (default: no)\n'
        '  -h   or --help                This text\n'
        )

#######################################
def main():
    '''main()

    Parse options and call check_queue().
    '''
    msg('queue_repair.py v. %s\n'
        'Copyright (C) 2001 %s' % (__version__, __author__))
    msg('Licensed under the GNU General Public License version 2\n')

    optionlist = 's:bnrthc'
    longoptionlist = ('split=', 'bigtodo', 'no-bigtodo', 'repair', 'test',
        'i-want-a-broken-conf-split', 'help', 'create')
    force_split = None
    force_bigtodo = None
    test = 1
    qmaildir = confqmail
    mathishard = 0
    create = 0

    try:
        options, args = getopt.getopt(sys.argv[1:], optionlist, longoptionlist)

        for (option, value) in options:
            if option in ('-s', '--split'):
                try:
                    force_split = int(value)
                    if force_split < 1:
                        raise ValueError
                except ValueError:
                    raise getopt.error, 'split value must be a positive integer (%s)' % value
            elif option in ('-n', '--no-bigtodo'):
                force_bigtodo = -1
            elif option in ('-b', '--bigtodo'):
                force_bigtodo = 1
            elif option in ('-r', '--repair'):
                test = 0
            elif option in ('-t', '--test'):
                test = 1
            elif option == '--i-want-a-broken-conf-split':
                mathishard = 1
            elif option in ('-h', '--help'):
                show_help()
                sys.exit(0)
            elif option in ('-c', '--create'):
                create = 1
        if args:
            if len(args) > 1:
                raise getopt.error, 'conf-qmail must be a single argument (%s)' % string.join(args)
            qmaildir = args[0]

    except getopt.error, o:
        err('Error:  %s' % o, showhelp=1)

    check_queue(qmaildir, test, force_split, force_bigtodo, create, mathishard)

#######################################
if __name__ == '__main__':
    main()
