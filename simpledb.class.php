<?php
/**
 * SimpleDB - a lightweight and easy-to-use wrapper for simple database operations using PDO in PHP.
 * 
 * See below for detailed usage and README.md file for more information.
 *
 * @author Kevin P. Siu
 * @copyright Copyright (c) 2012 Kevin P. Siu
 * @license http://www.opensource.org/licenses/mit-license.php Released under the MIT License
 **/
class SimpleDB{
    private $pdo;   //PDO instance
    
    public function __construct($dsn, $user='', $pass=''){
        $this->pdo = new PDO($dsn, $user, $pass);
    }
    
    public function query($sql, $data = null){
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Inserts an array of values into a table of the database.
	 * @param  string $table The name of the table to insert data into
	 * @param  array  $data  An associative array of the data to insert. The array key must correspond to the column name and the value is the data to insert 
	 * @return boolean       TRUE on success, FALSE on failure
     */
    public function insert($table, $data){
        $sql = "INSERT INTO $table (" . implode(array_keys($data), ', ') . ") VALUES (:" . implode(array_keys($data), ', :') . ");";
        $bind = array();
        foreach ($data as $field => $value){
            $bind[":$field"] = $value;
        }
        return $this->query($sql, $bind);
    }
    
	/**
	 * Selects all rows from the chosen table matching the conditions.
	 * @param  string  $table      Name of table to select from
	 * @param  array   $conditions Associative array of conditions where the key corresponds to column name and the value is the condition
	 * @param  string  $sortby     (Optional) The column to sort results by
	 * @param  boolean $sortdesc   (Optional) If true, results will be sorted in descending order of $sortby
	 * @return array   An array containing all selected rows. Each row in the array is an associative array of column values
	 */
    public function select($table, $conditions, $sortby = null, $sortdesc = false){
        $sql = "SELECT * FROM $table WHERE";
        $bind = array();
        $delim = '';
        foreach ($conditions as $field => $value){
            $sql .= "$delim $field=:$field";
            $delim = ' AND';
            $bind[":$field"] = $value;
        }
        
        if ($sortby !== null){
            $sql .= " ORDER BY $sortby";
            if ($sortdesc){
                $sql .= " DESC";
            }
        }

        $sql .= ';';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bind);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        return $stmt->fetchAll();
    }
    
	/**
	 * Selects a first row from the chosen table matching the conditions.
	 * @param  string  $table      Name of table to select from
	 * @param  array   $conditions Associative array of conditions where the key corresponds to column name and the value is the condition
	 * @param  string  $sortby     (Optional) The column to sort results by
	 * @param  boolean $sortdesc   (Optional) If true, results will be sorted in descending order of $sortby
	 * @return array   An associative array of column values for the selected row
	 */
    public function select_single_row($table, $conditions, $sortby = null, $sortdesc = false){
        $sql = "SELECT * FROM $table WHERE";
        $bind = array();
        $delim = '';
        foreach ($conditions as $field => $value){
            $sql .= "$delim $field=:$field";
            $delim = ' AND';
            $bind[":$field"] = $value;
        }

        if ($sortby !== null){
            $sql .= " ORDER BY $sortby";
            if ($sortdesc){
                $sql .= " DESC";
            }
        }
        
        $sql .= ';';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bind);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        return $stmt->fetch();
    }

	/**
	 * Selects a single value from the first row from the chosen table matching the conditions.
	 * @param  string  $table      Name of table to select from
	 * @param  array   $conditions Associative array of conditions where the key corresponds to column name and the value is the condition
 	 * @param  string  $column     Column name to select
	 * @param  string  $sortby     (Optional) The column to sort results by
	 * @param  boolean $sortdesc   (Optional) If true, results will be sorted in descending order of $sortby
	 * @return array   An associative array of column values for the selected row
	 */    
    public function select_single_value($table, $conditions, $column, $sortby = null, $sortdesc = false){
        $sql = "SELECT $column FROM $table WHERE";
        $bind = array();
        $delim = '';
        foreach ($conditions as $field => $value){
            $sql .= "$delim $field=:$field";
            $delim = ' AND';
            $bind[":$field"] = $value;
        }

        if ($sortby !== null){
            $sql .= " ORDER BY $sortby";
            if ($sortdesc){
                $sql .= " DESC";
            }
        }
        $sql .= ';';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchColumn();
    }

	/**
	 * Counts the number of rows in the table matching the search conditions.
	 * @param  string  $table      Name of table to count in
	 * @param  array   $conditions Associative array of conditions where the key corresponds to column name and the value is the condition
	 * @return int     The number of rows in the table matching given condition
	 */
    public function count($table, $conditions){
        $sql = "SELECT COUNT(*) FROM $table WHERE";
        $bind = array();
        $delim = '';
        foreach ($conditions as $field => $value){
            $sql .= "$delim $field=:$field";
            $delim = ' AND';
            $bind[":$field"] = $value;
        }
        $sql .= ';';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bind);
        return intval($stmt->fetchColumn());
    }
    
	/**
	 * Updates the table with new values given a set of conditions.
	 * @param  string  $table      Name of table to update rows in
	 * @param  array   $data       Associative array of conditions where the key corresponds to column name and the value is the data	
	 * @param  array   $conditions Associative array of conditions where the key corresponds to column name and the value is the condition
	 * @return boolean TRUE on success, FALSE on failure
	 */
    public function update($table, $data, $conditions){
        $sql = "UPDATE $table SET";
        $bind = array();
        $delim = '';
        foreach ($data as $field => $value){
            $sql .= "$delim $field=:$field";
            $delim = ',';
            $bind[":$field"] = $value;
        }
        $sql .= " WHERE";
        
        $delim = '';
        foreach ($conditions as $field => $value){
            $sql .= "$delim $field=:where_$field";
            $delim = ' AND';
            $bind[":where_$field"] = $value;
        }
        $sql .= ';';

        return $this->query($sql, $bind);
    }

	/**
	 * Inserts given data into the table OR updates the row if the row with the given primary key already exists.
	 *
	 * This method is a basic UPSERT (UPDATE OR INSERT) implementation which 
	 * requires the table to have at least one primary key or unique value.
	 * The method also supports composite primary keys as long as they are all
	 * passed into the $primarykey parameter. Currently only tested on SQLite
	 * but theoretically supports MySQL as well.
	 * 
	 * @param  string  $table      Name of table to update rows in
	 * @param  array   $data       Associative array of conditions where the key corresponds to column name and the value is the data	
	 * @param  array   $primarykey Associative array of primary key-val pairs (including support for composite PKs)
	 * @return boolean TRUE on success, FALSE on failure
	 */
    public function upsert($table, $data, $primarykey){
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $mergedData = array_merge($primarykey, $data);

        if($driver == 'sqlite'){            
            $sql = "INSERT OR IGNORE INTO $table (" . implode(array_keys($mergedData), ', ') . ") VALUES (:" . implode(array_keys($mergedData), ', :') . ");";
            $bind = array();
            foreach ($mergedData as $field => $value){
                $bind[":$field"] = $value;
            }
            $this->query($sql, $bind);
            return $this->update($table, $data, $primarykey);        
        }
        //Haven't tested this yet in MySQL... hope it works
        else if($driver == 'mysql'){
            $sql = "INSERT INTO $table (" . implode(array_keys($mergedData), ', ') . ") VALUES (:" . implode(array_keys($mergedData), ', :') . ")";
            $bind = array();
            foreach ($mergedData as $field => $value){
                $bind[":$field"] = $value;
            }
            $sql .= " ON DUPLICATE KEY UPDATE ";
            $delim = '';
            foreach ($data as $field => $value){
                $sql .= "$delim $field=:update_$field";
                $delim = ',';
                $bind[":update_$field"] = $value;
            }
            $sql .= " WHERE";

            $delim = '';
            foreach ($primarykey as $field => $value){
                $sql .= "$delim $field=:where_$field";
                $delim = ' AND';
                $bind[":where_$field"] = $value;
            }
            $sql .= ';';

            return $this->query($sql, $bind);
        }
    }

	/**
	 * Deletes a row from the table given a set of conditions.
	 * @param  string  $table      Name of table to delete rows from
	 * @param  array   $conditions Associative array of conditions where the key corresponds to column name and the value is the condition
	 * @return boolean TRUE on success, FALSE on failure
	 */
    public function delete($table, $conditions){
        $sql = "DELETE FROM $table WHERE";

        $bind = array();
        $delim = '';
        foreach ($conditions as $field => $value){
            $sql .= "$delim $field=:$field";
            $delim = ' AND';
            $bind[":$field"] = $value;
        }
        $sql .= ';';
        
        return $this->query($sql, $bind);
    }

    public function close(){
        $this->pdo = null;
    }
       
    public function __destruct(){
        $this->pdo = null;
    }
}
?>