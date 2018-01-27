<?php
class Database
{
    private $host;
    private $dbName;
    const CHARSET = 'utf8';
    const USER = 'root';
    const PASSWORD = '';
    private $connection;


    public function __construct($host, $dbName)
    {
        $this->host = $host;
        $this->dbName = $dbName;
        $this->connection = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';charset=' . self::CHARSET, self::USER, self::PASSWORD);
    }

    public function select($table, $selectColumns, $colWhere, $valWhere, $fetchAll, $not)
    {
        if(!is_array($colWhere))
        {
            $colWhere = array($colWhere);
            $valWhere = array($valWhere);
        }

        $mysql_pdo_error = false;
        $conditions = "";

        foreach ($colWhere as $i => $col)
        {
            // pridat AND
            if ($conditions != "") $conditions .= "AND ";

            $conditions .= "`$col` = ? ";
        }

        $query = "SELECT $selectColumns FROM `".$table."` WHERE $not $conditions;";
        $statement = $this->connection->prepare($query);

        if ($valWhere != null)
        {
            foreach ($valWhere as $i => $val)
            {          
                $statement->bindValue($i+1, $val);  
            }
        }

        $statement->execute();

        $errors = $statement->errorInfo();

        if ($errors[0] + 0 > 0)
        {
            $mysql_pdo_error = true;
        }

        if ($mysql_pdo_error == false)
        {
            if($fetchAll)
                $res = $statement->fetchAll(PDO::FETCH_ASSOC);
            else
                $res = $statement->fetch(PDO::FETCH_ASSOC);

            //echo "<pre></pre><pre>". print_r($valWhere, true)."</pre>";
            //echo $query;
            //echo "<pre>" . print_r($res,true) . "</pre>";
            //exit();

            return $res;
        }
        else
        {
            echo "<pre></pre><pre>". print_r($statement, true)."</pre>";
            echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
            print_r($errors);
            echo "<pre></pre><pre>". print_r($valWhere, true)."</pre>";
            echo "<pre>SQL dotaz: $query</pre>";
            //exit();
        }
    }


    public function insert($table, $arrayColumns, $arrayValues)   
    {
        $mysql_pdo_error = false;

        $insert_columns = "";
        $insert_values  = "";

        if ($arrayColumns != null)
        {
            foreach ($arrayColumns as $col)
            {
                if ($insert_columns != "") $insert_columns .= ", ";
                if ($insert_columns != "") $insert_values .= ", ";
                $insert_columns .= "`$col`";
                $insert_values .= "?";
            }
        }

        $query = "INSERT INTO `$table` ($insert_columns) VALUES ($insert_values);";
        $statement = $this->connection->prepare($query);

        if ($arrayValues != null)
        {
            foreach ($arrayValues as $i => $val)
            {
                $statement->bindValue($i+1, $val);  
            }
        }

        $statement->execute();
        $errors = $statement->errorInfo();

        if ($errors[0] + 0 > 0)
        {
            echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
            printr($errors);
            echo "SQL dotaz: $query";
            exit();
        }
        else
        {
            $item_id = $this->connection->lastInsertId();
            return $item_id;
        }
    }

    public function update($table, $colWhere, $valWhere, $arrayColumns, $arrayValues)
    {
        if(!is_array($colWhere))
        {
            $colWhere = array($colWhere);
            $valWhere = array($valWhere);
        }

        $mysql_pdo_error = false;
        $conditions = "";
        $newValues = "";
        $valuesToBind = array();


        foreach($arrayColumns as $i => $col)
        {
            if($newValues != "") $newValues .= ", ";

            $newValues .= "`$col` = ? "; 

            //if(DateTime::createFromFormat('Y-m-d G:i:s', $arrayValues[$i]) !== FALSE)
            //    $valuesToBind[$i+1] = "CURRENT_TIMESTAMP()";
            //else
                $valuesToBind[$i+1] = "$arrayValues[$i]";
            
            
            //echo "<pre>Tady: ". ($i+1). ": " .$arrayValues[$i]." </pre>";
        }
        
        foreach ($colWhere as $i => $col)
        {
            if ($conditions != "") $conditions .= "AND ";

            if(strpos($valWhere[$i], 'NULL') !== false)
                $conditions .= "`$col` IS ? ";
            else
                $conditions .= "`$col` = ? ";

                //echo "<pre>Tuuuuuu: ". (count($arrayColumns)+$i+1). ": ".$valWhere[$i] ."</pre>";
            $valuesToBind[(count($arrayColumns)+$i+1)] = "$valWhere[$i]";
        }

        $query = "UPDATE `$table` SET $newValues WHERE $conditions;";
        $statement = $this->connection->prepare($query);

        if ($valuesToBind != null)
        {
            foreach ($valuesToBind as $i => $val)
            {
                //echo $i . ", ";
                $res = $statement->bindValue($i, $val);
            }
        }
        
        //echo $query;
        //echo "<pre>". $newValues ."<pre>";
        //echo "<pre>". $conditions ."<pre>";
        //echo "<pre>LALALA:". print_r($valuesToBind,true) ."<pre>";
        //exit();
        
        $result = $statement->execute();
        $errors = $statement->errorInfo();

        if(!$result)
        {
            //echo "!RESULT</br>";
            //exit();
        }
        
        if ($errors[0] + 0 > 0)
        {
            $mysql_pdo_error = true;
        }

        if ($mysql_pdo_error == false)
        {
            $item_id = $this->connection->lastInsertId();
            
            //echo $item_id;
            //exit();
            
            return $item_id;
        }
        else
        {
            echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
            print_r($errors);
            echo "SQL dotaz: $query";
            exit();
        }
    }


    public function delete($table, $column, $value)
    {
        $row = $this->select($table, "*", $column, $value, false, '');
        $idKey = key($row);
        $id = $row[$idKey];

        $query = "DELETE FROM `".$table."` WHERE `$column` = ?;";
        $statement = $this->connection->prepare($query);

        $statement->bindValue(1, $value);  

        $statement->execute();

        $errors = $statement->errorInfo();

        if ($errors[0] + 0 > 0)
        {
            echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
            printr($errors);
            echo "SQL dotaz: $query";
            //exit();
        }
        else
        {
            echo $query;
            //exit();
        }
    }

    public function getTable($table)
    {
        $mysql_pdo_error = false;

        $query = "SELECT * FROM `$table`;";
        $statement = $this->connection->prepare($query);

        $statement->execute();

        $errors = $statement->errorInfo();

        if ($errors[0] + 0 > 0)
        {
            $mysql_pdo_error = true;
        }

        if ($mysql_pdo_error == false)
        {
            $res = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }
        else
        {
            echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
            print_r($errors);
            echo "SQL dotaz: $query";
        }
    }
}