ADOdb Library for PHP5
======================

[![Join chat on Gitter](https://img.shields.io/gitter/room/form-data/form-data.svg)](https://gitter.im/adodb/adodb?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

(c) 2000-2013 John Lim (jlim@natsoft.com)  
(c) 2014      Damien Regad, Mark Newnham and the ADOdb community

Released under both [BSD 3-Clause](https://github.com/ADOdb/ADOdb/blob/master/LICENSE.md#bsd-3-clause-license) 
and [GNU Lesser GPL library 2.1](https://github.com/ADOdb/ADOdb/blob/master/LICENSE.md#gnu-lesser-general-public-license) 
licenses.  
This means you can use it in proprietary products; 
see [License](https://github.com/ADOdb/ADOdb/blob/master/LICENSE.md) for details.

Home page: http://adodb.sourceforge.net/

> **WARNING: known issue with Associative Fetch Mode in ADOdb v5.19
-- PLEASE UPGRADE TO v5.20 !**  
> When fetching data in Associative mode (i.e. when `$ADODB_FETCH_MODE` is
> set to *ADODB_FETCH_ASSOC*), recordsets do not return any data (empty strings)
> when using some database drivers. The problem has been reported on MSSQL,
> Interbase and Foxpro, but possibly affects other drivers as well; all drivers
> derived from the above are also impacted.
> For further details, please refer to [Issue #20](https://github.com/ADOdb/ADOdb/issues/20).


Introduction
============

PHP's database access functions are not standardized. This creates a
need for a database class library to hide the differences between the
different databases (encapsulate the differences) so we can easily
switch databases.

The library currently supports MySQL, Interbase, Sybase, PostgreSQL, Oracle,
Microsoft SQL server,  Foxpro ODBC, Access ODBC, Informix, DB2,
Sybase SQL Anywhere, generic ODBC and Microsoft's ADO.

We hope more people will contribute drivers to support other databases.


Installation
============

Unpack all the files into a directory accessible by your web server.

To test, try modifying some of the tutorial examples.
Make sure you customize the connection settings correctly.

You can debug using:

``` php
<?php
include('adodb/adodb.inc.php');

$db = ADONewConnection($driver); # eg. 'mysql' or 'oci8'
$db->debug = true;
$db->Connect($server, $user, $password, $database);
$rs = $db->Execute('select * from some_small_table');
print "<pre>";
print_r($rs->GetRows());
print "</pre>";
```


Documentation and Examples
==========================

Refer to the `docs` directory for library documentation and examples.

- Main documentation: `docs-adodb.htm`.
  Query, update and insert records using a portable API.
- Data dictionary docs: `docs-datadict.htm`.
  Describes how to create database tables and indexes in a portable manner.
- Database performance monitoring docs: `docs-perf.htm`.
  Allows you to perform health checks, tune and monitor your database.
- Database-backed session docs: `docs-session.htm`.

There is also a tutorial `tute.htm` that contrasts ADOdb code with
mysql code.


Files
=====

- `adodb.inc.php` is the library's main file. You only need to include this file.
- `adodb-*.inc.php` are the database specific driver code.
- `adodb-session.php` is the PHP4 session handling code.
- `test.php` contains a list of test commands to exercise the class library.
- `testdatabases.inc.php` contains the list of databases to apply the tests on.
- `Benchmark.php` is a simple benchmark to test the throughput of a SELECT
statement for databases described in testdatabases.inc.php. The benchmark
tables are created in test.php.


Support
=======

To discuss with the ADOdb development team and users, connect to our
[Gitter chatroom](https://gitter.im/adodb/adodb) using your Github credentials.

Please report bugs, issues and feature requests on Github:

https://github.com/ADOdb/ADOdb/issues

You may also find legacy issues in

- the old [ADOdb forums](http://phplens.com/lens/lensforum/topics.php?id=4) on phplens.com
- the [SourceForge tickets section](http://sourceforge.net/p/adodb/_list/tickets)

However, please note that they are not actively monitored and should
only be used as reference.
