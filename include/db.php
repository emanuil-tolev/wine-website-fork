<?php

/*
  WineHQ
  DB Wrapper Class
  by Jeremy Newman <jnewman@codeweavers.com>
*/  
 
class db 
{
    var $dbcon;
    var $db;

    // init db class
    function db ($dbuser, $dbpass, $dbhost, $db)
    {
        // open database
        $this->db = $db;
        $this->dbcon = $this->open($dbuser, $dbpass, $dbhost, $db);
        global $html;
        $html->errorlog .= "open: ".$this->db." -> ".$this->dbcon."<br>\n";
    }

    // connect to db
    function open ($dbuser, $dbpass, $dbhost, $db)
    {
        global $html;
        
        // Return if DB is already open
        if($this->dbcon > 0)
	        return $this->dbcon;

        // Make Connection
        $this->dbcon = @mysql_pconnect($dbhost, $dbuser, $dbpass);
        
        // exit if error
        if(!$this->dbcon) { $this->handle_error("database connection error"); }

        // Select Our DB
        $this->db = $db;
        mysql_select_db($this->db, $this->dbcon);

        // return connection string
        return $this->dbcon;
    }

    // close db connection
    function close ()
    {
        // return if DB not open
        if(!$this->dbcon)
	        return;

        // close DB connection
        mysql_close($this->dbcon);
    }

    // perform query
    function query ($q)
    {
        global $html;

        // log the query
        $html->errorlog .= "query: ".$this->db." -> ".$this->dbcon." <=> ".$q."<br>\n";

        // return if DB not open
        if($this->dbcon)
        {
            // Select Our DB (needed for multiple DB support)
            mysql_select_db($this->db, $this->dbcon);

            // perform query
            $result = @mysql_query($q, $this->dbcon);

            // die if error
            if (!$result) { $this->handle_error(mysql_error($this->dbcon), $q); }
        }

	    // return
	    return $result;
    }

    // fetch query result into object
    function fetch ($result)
    {
	    // get object from result
	    $ob = mysql_fetch_object($result);
	    return $ob;
    }

    // fetch query result into array
    function fetch_array ($result)
    {
	    // get object from result
	    $row = mysql_fetch_row($result);
	    return $row;
    }

    // get number of rows returned from query
    function num_rows ($result)
    {
	    return @mysql_num_rows($result);
    }

    // get last insert id from insert query
    function insert_id ()
    {
	    return @mysql_insert_id($this->dbcon);
    }

    // return a list of the fields in a table
    function list_fields ($table)
    {
        if (!$table) return null;
        $fields = mysql_list_fields($this->db, $table, $this->dbcon);
        $columns = mysql_num_fields($fields);
        $arr = array();
        for ($i = 0; $i < $columns; $i++)
        {
            array_push($arr, mysql_field_name($fields, $i));
        }
        return $arr;
    }

    // select wrapper
    function select ($table, $select = "*", $where = null, $orderby = null, $sort = null, $groupby = null, $limit = null)
    {
        if (isset($where)) { $where = "WHERE ".$where; }
        if (isset($orderby)) { $orderby = "ORDER BY ".$orderby; }
        if (isset($groupby)) { $orderby = "GROUP BY ".$orderby; }
        if (isset($limit)) { $orderby = "LIMIT ".$orderby; }
        $q = "SELECT ".$select." FROM `".$table."` ".$where." ".$orderby." ".$sort." ".$groupby." ".$limit;
        return $this->query($q);
    }

	// insert into table
	function insert ($table, $fields, $values)
	{
        // create query
        $query = "INSERT INTO `".$table."` VALUES (";
        foreach ($fields as $field)
        {
            $query .= "'".addslashes($values[$field])."'";
            if ($field != end($fields)) { $query .= ","; } 
        }
        $query .= ")";
        // execute query
        $result = $this->query($query);
        if (!$result) { return null; }
        // return id
        return $this->insert_id();
    }
    
	// update item in table
	function update ($table, $fields, $values, $key, $id)
	{
		if (!$id) { return 0; }
        reset($fields);
        foreach ($fields as $field)
        {
            if (!isset($values[$field])) { continue; }
            $this->query("UPDATE `".$table."` SET `".$field."` = '".addslashes($values[$field])."' WHERE ".$key." = '".addslashes($id)."'");
        }
		return 1;
	}

	// remove item from table
	function remove ($table, $key, $id)
	{
		if (!$id) { return 0; }
		$result = $this->query("DELETE FROM `".$table."` WHERE `".$key."` = '".addslashes($id)."'");
		if (!$result) { return 0; }
		return 1;
	}

	// lookup and return a single value
	function lookup ($table, $key, $id, $value)
	{
		$result = $this->select($table, addslashes($value), $key." = '".addslashes($id)."'");
		$ob = $this->fetch($result);
		return $ob->$value;
	}

    // error handler for DB connections
    function handle_error ($message = null, $query = null)
    {
        global $config, $html;
        if ($config->debug)
        {
            // log error
            $error_line = date("Y/m/d H:i:s", time())." - "."DB"." - ".$this->db." - ".$message." - ".$query."\n";
            error_log($error_line, 3, $config->error_log);
        }
        // display error
        $html->error_page($message);
        exit();
    }

// end class DB
}
?>
