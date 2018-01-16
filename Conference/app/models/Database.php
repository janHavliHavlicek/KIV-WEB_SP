<?php 
class Database
{
    private $host;
    private $dbName;
    const CHARSET = 'utf8';
    const USER = 'root';
    const PASSWORD = '';

    private $database;

    public function __construct($host, $dbName)
    {
        $this->host = $host;
        $this->dbName = $dbName;

        $this->init();
    }

    private function init()
    {
        $this->database = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';charset=' . self::CHARSET, self::USER, self::PASSWORD);
    }

    public function insert($table, $arrayColumns, $arrayValues)
    {
        foreach($arrayValues as &$val)
        {
            $val = "'" . $val . "'";
        }

        $columns = implode(",", $arrayColumns);
        $values = implode(",", $arrayValues);

        $stmt = $this->database->prepare("INSERT INTO " . $table . "(" . $columns . ") VALUES(" . $values . ")");

        $stmt->execute();
    }

    //BIND!
    public function select($table, $colWhere, $valWhere, $fetchAll, $not)
    {
        //echo "$table";
        //echo "<pre>". print_r($colWhere, true) . "</pre>";
        //echo "<pre>". print_r($valWhere, true) . "</pre>";

        if(is_array($colWhere) == true)
        {
            foreach($valWhere as &$val)
            {
                if(!(strpos($val, 'NULL') !== false))
                    $val = "'" . $val . "'";
            }

            for($i = 0; $i < count($colWhere); $i++)
            {
                if(strpos($valWhere[$i], 'NULL') !== false)
                    $newValuesArr[$i] = $colWhere[$i] . " IS " . $valWhere[$i];
                else
                    $newValuesArr[$i] = $colWhere[$i] . " = " . $valWhere[$i];
            }

            $newValues = implode(" AND ", $newValuesArr);

            //echo "SELECT * FROM " . $table . " WHERE " . $not . $newValues;

            $stmt = $this->database->prepare("SELECT * FROM " . $table . " WHERE " . $not . $newValues);
            $stmt->execute();
            if($fetchAll)
                $res = $stmt->fetchAll();
            else
                $res = $stmt->fetch();
        }
        else
        {
            $stmt = $this->database->prepare("SELECT * FROM " . $table . " WHERE " . $not . $colWhere . " = '" . $valWhere . "'");

            $stmt->execute();

            if($fetchAll)
                $res = $stmt->fetchAll();
            else
                $res = $stmt->fetch();
        }

        return $res;
    }

    public function selectAVG($table, $colWhere, $valWhere, $colAVG)
    {
        if(is_array($colWhere) == true)
        {
            foreach($valWhere as &$val)
            {
                if($val != 'NULL')
                    $val = "'" . $val . "'";
            }

            for($i = 0; $i < count($colWhere); $i++)
            {
                $newValuesArr[$i] = $colWhere[$i] . " = " . $valWhere[$i];
            }

            $newValues = implode("AND", $newValuesArr);

            $stmt = $this->database->prepare("SELECT AVG(" . $colAVG . ") FROM " . $table . " WHERE " . $newValues);

            $stmt->execute();
            $res = $stmt->fetch();
        }
        else
        {
            $stmt = $this->database->prepare("SELECT AVG(" . $colAVG . ") FROM " . $table . " WHERE " . $colWhere . " = '" . $valWhere . "'");

            $stmt->execute();
            $res = $stmt->fetch();
        }

        return $res;
    }

    /**
    * Selects desired row from table, find its id column name and id value
    * and deletes the row from the table given the found id credentials
    *
    * @param string $table 
    * @param string $column
    * @param string $value
    */
    public function delete($table, $column, $value)
    {
        $row = $this->select($table, $column, $value, false, '');
        $idKey = key($row);
        $id = $row[$idKey];

        $stmt = $this->database->prepare("DELETE FROM " . $table . " WHERE " . $idKey . " = '" . $id . "'");

        $stmt->execute();
    }

    public function getTable($table)
    {
        $stmt = $this->database->prepare("SELECT * FROM " . $table);

        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res;
    }

    /*$this->database->update('user', 'username', 'Test', array('status', 'mail'), array('reviewer', 'ahoj@test.cz'));

    DevDoc: Problem when inputs are not an arrays!
    */
    public function update($table, $colWhere, $valWhere, $arrayColumns, $arrayValues)
    {
        foreach($arrayValues as &$val)
        {
            if($val != 'NULL')
                $val = "'" . $val . "'";
        }

        for($i = 0; $i < count($arrayColumns); $i++)
        {
            $newValuesArr[$i] = $arrayColumns[$i] . " = " . $arrayValues[$i];
        }

        $newValues = implode(",", $newValuesArr);

        //echo "UPDATE " . $table . " SET " . $newValues . " WHERE " . $colWhere . " = '" . $valWhere . "'";
        //exit();
        
        $stmt = $this->database->prepare("UPDATE " . $table . " SET " . $newValues . " WHERE " . $colWhere . " = '" . $valWhere . "'");

        $stmt->execute();
    }
}
?>