<?php

namespace Nebiros\PhpUtils\Db;

/**
 * Mysql wrapper, to handle some mysql_* functions in a OOP way.
 *
 * @author nebiros
 */
class Mysql {
    const DEFAULT_LIMIT = 10;
    const DEFAULT_OFFSET = 0;
    const DEFAULT_PAGE = 1;
    
    const FETCH_ASSOC = 1;
    const FETCH_OBJ = 2;

    /**
     *
     * @var array
     */
    protected $_defaultOptions = array(
        "host" => "localhost",
        "username" => "root",
        "password" => "",
        "dbname" => "",
        "fetch_mode" => self::FETCH_ASSOC,
        "charset" => "utf8",
        "syslog_queries" => false,
        "syslog_ignore_from_tables" => ""
    );
    
    /**
     *
     * @var array
     */
    protected $_options = array();
    
    /**
     *
     * @var resource
     */
    protected $_resource = null;
    
    /**
     *
     * @var string
     */
    protected $_query = null;
    
    /**
     *
     * @var int
     */
    protected $_page = self::DEFAULT_PAGE;

    /**
     *
     * @var int
     */
    protected $_lastPage = null;

    /**
     *
     * @var int
     */
    protected $_limitCount = null;
    
    /**
     *
     * @var int
     */
    protected $_limitOffset = null;
    
    /**
     *
     * @var int
     */
    protected $_totalRows = 0;
    
    /**
     *
     * @param array|string $options
     */
    public function __construct($options = null) {
        $this->setDefaultOptions();
        
        if (true === is_array($options)) {
            if (false === empty($options)) {
                $this->setOptions($options);
            }            
        } else if (true === is_string($options)) {
            $tmp = $options;
            $options = realpath($tmp);
            $ext = strtolower(pathinfo($options, PATHINFO_EXTENSION));

            if ($ext != "ini") {
                throw new \Exception("Configuration file '{$tmp}' must be a ini file");
            }
            
            if (false === is_file($options)) {
                throw new \Exception("Configuration file '{$tmp}' not found");
            }

            $options = parse_ini_file($options, true);
            $env = $options[APPLICATION_ENV];
            
            if (true === empty($env)) {
                throw new \Exception("Configuration section '" . APPLICATION_ENV . "' not found");
            }
            
            $this->setOptions($env);
        }
    }

    /**
     * Set options.
     *
     * @param array $options
     * @return Nebiros\PhpUtils\Db\Mysql
     */
    public function setOptions(Array $options) {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }

    /**
     * Reset to default options.
     *
     * @return Nebiros\PhpUtils\Db\Mysql
     */
    public function clearOptions() {
        $this->_options = $this->_defaultOptions;
        return $this;
    }

    /**
     * Default options.
     *
     * @return Nebiros\PhpUtils\Db\Mysql
     */
    public function setDefaultOptions() {
        $this->_options = $this->_defaultOptions;
        return $this;
    }

    /**
     * Get default options.
     *
     * @return array
     */
    public function getDefaultOptions() {
        return $this->_defaultOptions;
    }

    /**
     * Set option.
     *
     * @param mixed $key
     * @param mixed $value
     * @return Nebiros\PhpUtils\Db\Mysql
     */
    public function setOption($key, $value = null) {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * Get option.
     *
     * @param mixed $key
     * @param null|mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null) {
        if (true === isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return $default;
    }
    
    /**
     *
     * @param array $options
     * @return Nebiros\PhpUtils\Db\Mysql 
     */
    public function addOptions(Array $options) {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }
    
    /**
     *
     * @param mixed $key
     * @param mixed $value
     * @return Nebiros\PhpUtils\Db\Mysql 
     */
    public function addOption($key, $value = null) {
        $this->_options[$key] = $value;
        return $this;
    }    

    /**
     * Connect to mysql.
     *
     * @return Nebiros\PhpUtils\Db\Mysql
     */
    public function connect() {
        try {
            if (false === (
                $this->_resource = mysql_connect(
                    $this->getOption("host"),
                    $this->getOption("username"),
                    $this->getOption("password")
            ))) {
                throw new \Exception("mysql_connect() function error (" . mysql_error() . ")");
            }
            
            if (false === mysql_set_charset($this->getOption("charset"), $this->_resource)) {
                throw new \Exception("mysql_set_charset() function error (" . mysql_error() . ")");
            }

            if (false === mysql_select_db($this->getOption("dbname"))) {
                throw new \Exception("mysql_select_db() function error (" . mysql_error() . ")");
            }
        } catch (\Exception $e) {
            throw new \Exception("Can't connect ({$e->getMessage()})");
        }

        return $this;
    }

    /**
     * Get mysql connection resource.
     *
     * @return resource
     */
    public function getConnection() {
        return $this->_resource;
    }

    /**
     * 
     * @return void
     */
    public function disconnect() {
        try {
            if (false === mysql_close($this->_resource)) {
                throw new \Exception("mysql_close() function error (" . mysql_error() . ")");
            }
        } catch (\Exception $e) {
            throw new \Exception("Can't disconnect ({$e->getMessage()})");
        }
    }
    
    /**
     * 
     * @return void
     */
    public function beginTransaction() {
        @mysql_query("SET AUTOCOMMIT = 0", $this->_resource);
        @mysql_query("BEGIN", $this->_resource);
    }

    /**
     * 
     * @return void
     */    
    public function commit() {
        @mysql_query("COMMIT", $this->_resource);
    }

    /**
     * 
     * @return void
     */    
    public function rollBack() {
        @mysql_query("ROLLBACK", $this->_resource);
    }
    
    /**
     *
     * @param string|array $query
     * @return Nebiros\PhpUtils\Db\Mysql 
     */
    public function setQuery($query) {
        $this->_query = $this->buildQuery($query);
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getQuery() {
       return $this->_query; 
    }

    /**
     * Query database.
     *
     * @param string|array $query
     * @param bool $debug
     * @return resource
     */
    public function query($query = null, $debug = false) {
        if (null === $query) {
            $query = $this->_query;
        }
        
        $query = $this->buildQuery($query);
        
        try {
            if (true === $debug) {
                return $query;
            }
            
            if ($this->getOption("syslog_queries", false)) {
                $ignoreLog = false;
                
                if (strlen($this->getOption("syslog_ignore_from_tables", "")) > 0) {
                    $ignoreTables = $this->getOption("syslog_ignore_from_tables");
                    $ignoreTables = implode("|", explode(" ", $ignoreTables));
                    if (preg_match("#FROM ((.+)?(\.)?({$ignoreTables}))#i", $query)) {
                        $ignoreLog = true;
                    }
                }
                
                if (!$ignoreLog) {
                    openlog(__CLASS__ . " - " . __METHOD__, LOG_NDELAY | LOG_PID | LOG_PERROR, LOG_LOCAL0);
                    syslog(LOG_NOTICE, "QUERY - {$query}");
                    closelog();
                }                
            }

            if (false === ($result = mysql_query(trim($query), $this->_resource))) {
                throw new \Exception("mysql_query() function error (" . mysql_error() . ")");
            }
        } catch (\Exception $e) {
            throw new \Exception("Can't query this database ({$e->getMessage()})");
        }

        return $result;
    }

    /**
     * Insert data into the database, data is an associative array, column => value type.
     *
     * Example:
     * $db->insert("table1", array("col1" => "val1", "col2" => "val2"));
     *
     * @param string $table
     * @param array $data
     * @param bool $debug
     * @return bool
     */
    public function insert($table, Array $data, $debug = false) {
        try {
            foreach ($data AS $column => $value) {
                if ($value === null || strtolower($value) === "null" || $value === "") {
                    $data[$column] = "NULL";
                } else {
                    $data[$column] = "'" . mysql_real_escape_string($value, $this->_resource) . "'";
                }
            }

            $query = "INSERT INTO
                {$table}
                (`" . implode("`, `", array_keys($data)) . "`)
                VALUES
                (" . implode(", ", $data) . ")
            ";

            $result = $this->query($query, $debug);
        } catch (\Exception $e) {
            throw new \Exception("Can't insert data ({$e->getMessage()})");
        }

        return $result;
    }

    /**
     * Update data, data is an associative array, column => value type.
     *
     * Example:
     * $db->update("table1", array("col1" => "val1", "col2" => "val2"), "id = 1");
     *
     * @param string $table
     * @param array $data
     * @param string $where
     * @param bool $debug
     * @return bool
     */
    public function update($table, Array $data, $where = null, $debug = false) {
        try {
            $set = array();

            foreach ($data AS $column => $value) {
                if ($value === null || strtolower($value) == "null" || $value == "") {
                    $set[] = "`" . $column . "` = NULL";
                } else {
                    $set[] = "`" . $column . "` = '" . mysql_real_escape_string($value, $this->_resource) . "'";
                }
            }

            $query = "UPDATE
                {$table}
                SET " . implode(", ", $set) .
                (($where !== null) ? "\n WHERE {$where}" : null);

            $result = $this->query($query, $debug);
        } catch (\Exception $e) {
            throw new \Exception("Can't update data ({$e->getMessage()})");
        }

        return $result;
    }

    /**
     * Delete data.
     *
     * @param string $table
     * @param string $where
     * @param bool $debug
     * @return bool
     */
    public function delete($table, $where = null, $debug = false) {
        try {
            if ($where !== null) {
                $where = "WHERE " . $where;
            }

            $query = trim("DELETE FROM {$table} {$where}");
            $result = $this->query($query, $debug);
        } catch (\Exception $e) {
            throw new \Exception("Can't delete data ({$e->getMessage()})");
        }

        return $result;
    }

    /**
     * Get last inserted id from a table.
     *
     * @param string $table
     * @return int
     */
    public function lastInsertId($table = null) {
        try {
            $tableQuery = null;

            if (false === empty($table)) {
                $tableQuery = "FROM {$table}";
            }

            $query = trim("SELECT LAST_INSERT_ID() AS last_insertd_id {$tableQuery}");
            $result =  $this->query($query);

            if (false === $result) {
                throw new \Exception(mysql_error() . " (" . $query . ")");
            }

            $fetch = $this->fetchRow($result, self::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception("Can't get last insert id ({$e->getMessage()})");
        }

        return (int) $fetch["last_insertd_id"];
    }

    /**
     * Fetch a row.
     *
     * @param resource $result
     * @param int $mode
     * @return array|object
     */
    public function fetchRow($result, $mode = null) {
        if ($mode === null) {
            $mode = $this->getOption("fetch_mode");
        }

        switch ((int) $mode) {
            case self::FETCH_OBJ:
                return mysql_fetch_object($result);
                break;

            case self::FETCH_ASSOC:
                return mysql_fetch_assoc($result);
                break;

            default:
                throw new \Exception("fetch mode not supported");
                break;
        }
    }

    /**
     * Fetch all rows, each element key is a numeric index.
     *
     * @param resource $result
     * @return array
     */
    public function fetchAll($result) {
        $data = array();

        try {
            while ($row = $this->fetchRow($result)) {
                $data[] = $row;
            }
        } catch (\Exception $e) {
            throw new \Exception("Can't fetch data ({$e->getMessage()})");
        }

        return $data;
    }

    /**
     * Fetch all elements, each element key is the value of the first column, or
     * you can specify a column.
     *
     * @param resource $result
     * @param string $columnKey
     * @return array
     */
    public function fetchAssoc($result, $columnKey = null) {
        $data = array();

        try {
            while ($row = $this->fetchRow($result)) {
                if ($columnKey === null) {
                    $rowKeys = array_keys($row);
                    $columnKey = $rowKeys[0];
                }

                $data[$row[$columnKey]] = $row;
            }
        } catch (\Exception $e) {
            throw new \Exception("Can't fetch data ({$e->getMessage()})");
        }

        return $data;
    }
    
    /**
     * Limit SQL query.
     *
     * @param string|array $query
     * @param int $count How many rows per page
     * @param int $offset Row to start
     * @return string
     */
    public function limit($query = null, $count = null, $offset = null) {
        $count = intval($count);        
        if ($count <= 0) {
            throw new \Exception("Count '{$count}' is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new \Exception("Offset '{$offset}' is not valid");
        }
        
        if (null === $query) {
            $query = $this->_query;
        }
        
        $this->setTotalRows($query);
        $query = explode("\n", $query);
        $query[] = "LIMIT {$offset}, {$count}";
        $query = $this->buildQuery($query);
        $this->_limitCount = (int) $count;
        $this->_limitOffset = (int) $offset;
        $this->_lastPage = ceil($this->_totalRows / $this->_limitCount);

        return $query;
    }

    /**
     * Limit SQL query using a page number.
     *
     * @param int $page Page number
     * @param int $count How many rows per page
     * @return string
     */
    public function limitPage($page, $count) {
        $this->_page = ((int) $page > 0) ? (int) $page : 1;
        $this->_limitCount = ((int) $count > 0) ? (int) $count : 1;
        $this->_limitOffset = (int) $this->_limitCount * ($this->_page - 1);

        return $this->limit(null, $this->_limitCount, $this->_limitOffset);
    }

    /**
     *
     * @param string|array $query
     * @return Nebiros\PhpUtils\Db\Mysql
     */
    public function setTotalRows($query = null) {
        if (null === $query) {
            $query = $this->_query;
        }
        
        $query = $this->buildQuery(array(
            "SELECT COUNT(*) AS total_rows FROM",
            "({$query})",
            "AS db_select"
        ));
            
        try {                
            $data = $this->fetchRow($this->query($query));
        } catch (\Exception $e) {
            throw new \Exception("Can't get total rows ({$e->getMessage()})");
        }

        $this->_totalRows = (int) $data["total_rows"];
        return $this;
    }

    /**
     * Get total rows from a SQL query.
     *
     * @return int
     */
    public function count() {
        return $this->_totalRows;
    }
    
    /**
     *
     * @param string|array $query
     * @return string
     */
    public function buildQuery($query) {
        if (true === is_array($query)) {
            $query = implode("\n", $query);
        }
        
        return $query;
    }
    
    /**
     *
     * @return int
     */
    public function getPage() {
        return $this->_page;
    }
    
    /**
     *
     * @return int
     */
    public function getNextPage() {
        return $this->_page + 1;
    }
    
    /**
     *
     * @return int
     */
    public function getPreviousPage() {
        return $this->_page - 1;
    }
    
    /**
     *
     * @return int
     */
    public function getLastPage() {
        return $this->_lastPage;
    }
    
    /**
     *
     * @return int
     */
    public function getLimitCount() {
        return $this->_limitCount;
    }

    /**
     *
     * @return int
     */
    public function getLimitOffset() {
        return $this->_limitOffset;
    }
}