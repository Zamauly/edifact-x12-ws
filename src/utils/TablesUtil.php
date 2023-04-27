<?php
namespace App\Utils;

class TablesUtil{

    public function especifyTabDeclare(string $tableName = null,array $data = []) : string {
        $addignColumns = "";
        foreach($data as $column => $value){
            echo "<br /> column: ";
            var_dump($column);
            $notNull = ($value[10]==="TRUE")?"NOT NULL":"";
            $addignColumns .= "`".strtolower($value[0])."` {$value[2]}(".intval($value[3], 10).") ".$notNull.",";
            echo "<br /> value: ";
            var_dump($value);
        }
        $queryToCreateTable = "CREATE TABLE if not exists `".DB_NAME."`.`{$tableName}_catalogue` ("
                    ."`id` INT NOT NULL AUTO_INCREMENT,"
                    .$addignColumns
                    ."`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,"
                    ."`active` BOOLEAN DEFAULT TRUE,"
                    ."PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8";

        echo "<br /> queryToCreateTable: ".$queryToCreateTable."<br />";
        return "";
    }


}
?>