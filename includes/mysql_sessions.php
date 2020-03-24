<?php

# Lockable MySQL sessions handler (c) Alex/AT

# using GET_LOCK and some black voodoo magic to store sessions in the
# MySQL database while keeping ability to serialize requests
# a must on clustered servers, does not utilize ATS SQL classes

# SCHEMA:
#   CREATE TABLE `sessions` (
#     `id` VARCHAR(255),
#     `value` BLOB,
#     `updated` TIMESTAMP,
#     PRIMARY KEY (`id`),
#     INDEX (`updated`)
#   )

class MYSQL_SESSIONS
{
    var $server; # MySQL server (optionally :port)
    var $user; # MySQL user
    var $password; # MySQL password
    var $base; # MySQL database
    var $table; # MySQL table to use
    var $lockname = NULL; # named locks prefix, if NULL defaults to <base>.<table>

    var $compress = FALSE; # compress database data? DO NOT CHANGE IN REALTIME!
    var $compress_level = 9; # compression level

    var $lock_retry_sleep = 0; # seconds to sleep in PHP before lock retry
    var $lock_timeout = 10; # seconds to sleep in MySQL before lock retry
    var $lock_fail_timeout = 60; # seconds to fail reading session if locked

    var $dbid = NULL;
    
    function MYSQL_SESSIONS()
    {
    }

    # sets up and installs MySQL session handlers
    function init()
    {
        if ($this->lockname === NULL)
            $this->lockname = $this->base.'.'.$this->table;

        session_set_save_handler(
            array($this, '_open'),
            array($this, '_close'),
            array($this, '_read'),
            array($this, '_write'),
            array($this, '_destroy'),
            array($this, '_gc')
        );
    }

    function _open()
    {
        $this->dbid = mysql_connect($this->server, $this->user, $this->password, TRUE);
        if (!$this->dbid) return(FALSE);
		mysql_select_db($this->base, $this->dbid);
        return(mysql_query("SET NAMES UTF8"));
    }

    function _close()
    {
        if (!$this->dbid) return(FALSE);
        mysql_close($this->dbid);
        return(TRUE);
    }

    function _read($id)
    {
        if (!$this->dbid) die('');

        # lock session
        $this->_lock($id);

        # perform the read
        $res = mysql_query(
            'SELECT * FROM '.
            '`'.mysql_real_escape_string($this->table, $this->dbid).'`'.
            ' WHERE '.
            '(`id` = "'.mysql_real_escape_string($id, $this->dbid).'")'
        , $this->dbid);
        if (!$res) die('Session failure: READ_NO_RESULT');
        $row = mysql_fetch_assoc($res);
        if (!is_array($row)) return('');

        # done retrieving session data
        return($this->compress ? gzuncompress($row['value']) : $row['value']);
    }

    function _write($id, $data)
    {
        if (!$this->dbid) die('Session failure: WRITE_NO_CONNECT');

        # lock session
        $this->_lock($id);

        # write session
        $res = mysql_query(
            'REPLACE INTO '.
            '`'.mysql_real_escape_string($this->table, $this->dbid).'`'.
            ' (`id`, `value`) VALUES ('.
            '"'.mysql_real_escape_string($id, $this->dbid).'",'.
            '"'.mysql_real_escape_string($this->compress ? gzcompress($data, $this->compress_level) : $data, $this->dbid).'"'.
            ')'
        , $this->dbid);
        if (!$res) die('Session failure: WRITE_NO_RESULT');

        # done
        return(TRUE);
    }

    function _destroy($id)
    {
        if (!$this->dbid) die('Session failure: DESTROY_NO_CONNECT');

        # lock session
        $this->_lock($id);

        # perform the cleanup
        $res = mysql_query(
            'DELETE FROM '.
            '`'.mysql_real_escape_string($this->table, $this->dbid).'`'.
            ' WHERE '.
            '(`id` = "'.mysql_real_escape_string($id, $this->dbid).'")'
        , $this->dbid);
        if (!$res) die('Session failure: DESTROY_NO_RESULT');

        # done retrieving session data
        return(TRUE);
    }

    function _gc($lifetime)
    {
        if (!$this->dbid) die('Session failure: GC_NO_CONNECT');

        # perform the read
        $res = mysql_query(
            'DELETE FROM '.
            '`'.mysql_real_escape_string($this->table, $this->dbid).'`'.
            ' WHERE '.
            '(`updated` < "'.mysql_real_escape_string(date('Y-m-d H:i:s', time() - $lifetime), $this->dbid).'")'
        , $this->dbid);
        if (!$res) die('Session failure: GC_NO_RESULT');

        # done retrieving session data
        return(TRUE);
    }

    function _lock($id)
    {
        # the most hard part: the locking itself
        $time = 0;
        $stime = time();
        while (TRUE)
        {
            # try locking
            $res = mysql_query(
                'SELECT GET_LOCK('.
                '"'.mysql_real_escape_string($this->lockname.'_'.$id, $this->dbid).'",'.
                mysql_real_escape_string($this->lock_timeout, $this->dbid).
                ')'
            , $this->dbid);
            if (!$res) die('Session failure: LOCK_NO_RESULT');
            $row = mysql_fetch_row($res);
            if (!is_array($row)) die('Session failure: LOCK_NO_ROW');
            if ($row[0] == 1) break; # succesfully obtained lock
            
            # check, if we exceed the time
            $etime = time();
            if (($etime - $stime) > 0)
                $time += $etime - $stime; # prevent some NTP crap up
            if ($time > $this->lock_fail_timeout) die('Session failure: LOCK_TIMEOUT');
            $stime = $etime;
            sleep($this->lock_retry_sleep);
        }

        return(TRUE);
    }

    function _unlock($id)
    {
        $res = mysql_query(
            'SELECT RELEASE_LOCK('.
            '"'.mysql_real_escape_string($this->lockname.'_'.$id, $this->dbid).'"'.
            ')'
        , $this->dbid);
        if (!$res) die('Session failure: UNLOCK_NO_RESULT');
        $row = mysql_fetch_row($res);
        if (!is_array($row)) die('Session failure: UNLOCK_NO_ROW');

        return(TRUE);
    }
}

?>