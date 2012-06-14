SimpleDB
========

Quick Intro
-----------

SimpleDB - a lightweight and easy-to-use wrapper for simple database operations using PDO in PHP.

PDO is useful and portable, but it is often cumbersome and annoying to conduct
even the simplest database queries, which requires several lines of code like:

    $sth = $db->('SELECT * FROM table WHERE name=:name AND userid=:userid');
    $sth->execute(array(':name' => 'bob', ':userid' => 1));
    $rows = $sth->fetchAll();

SimpleDB makes basic and common database transactions easy - selecting rows looks like:

    $rows = $db->select('table', array('name' => 'bob')); //Gets all rows where name is bob

Quick Start
-----------

To start, simply drop the .php file into your directory of choice and
require() or include() as desired. Construct a SimpleDB object using the same
parameter strings as PDO (except the config strings, leave those out):

    $myDB = new SimpleDB('sqlite:databasename.db');

Then you can access the SimpleDB methods directly:

    $myDB->select();

Features
--------
* Parameter binding (to hopefully prevent some forms of SQL injection)
* Simple one-line queries
* Utility functions:
    * upsert (insert or update on duplicate) - also supports composite primary keys
    * count rows
    * get single column values

List of methods
-----------------

For detailed documentation and parameters see the source file 
Note that all array type parameters below require an associative array where
the key corresponds with the column name.

The output for the methods is  what you expect it to be (true/false for queries,
array of values for select, int for count, etc.).

    insert(string $table, array $data)

    select(string $table, array $conditions, [string $sortby, boolean $sortdesc])

    select_single_row(string $table, array $conditions, [string $sortby, boolean $sortdesc])

    select_single_value(string $table, array $conditions, string $column, [string $sortby, boolean $sortdesc])

    count(string $table, array $conditions)

    update(string $table, array $data, array $conditions)

    upsert(string $table, array $data, array $primarykey)

    delete(string $table, array $conditions)
