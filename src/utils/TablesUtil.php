<?php
namespace App\Utils;

class TablesUtil{

    public static array $indexKeys;
    public static array $foreignKeys;
    
    public function especifyTabDeclare(string $tableName = null,array $data = []) : mixed {
        $addignColumns = "";
        self::$indexKeys = [];
        self::$foreignKeys = [];
        foreach($data as $column => $value){
            $notNull = ($value[10]==="TRUE")?"NOT NULL":"";
            $columnName = strtolower($value[0]);
            if($value[6]==="TRUE"&&$value[7]!=="NULL"&&$value[8]!=="NULL"){
                $foreignKey = strtolower($value[8]);
                $referencedTable = strtolower($value[7]);
                if(!str_contains($referencedTable,","))
                    self::setAlterByForeignSentences($tableName,$columnName,$referencedTable,$foreignKey);
                else{
                    $foreignMultiKeys = explode(",",$foreignKey);
                    foreach (explode(",",$referencedTable) as $element => $elementValue)
                        self::setAlterByForeignSentences($tableName,$columnName,$elementValue,$foreignMultiKeys[$element]);
                        
                }
            } else if(isset($value[11]))
                if($value[11]==="TRUE")
                    array_push(self::$indexKeys,self::getIndexSentence($tableName,$columnName,true));

            $addignColumns .= "`".$columnName."` {$value[2]}(".intval($value[3], 10).") ".$notNull.",";
        }

        $queryToCreateTable = "CREATE TABLE if not exists `".DB_NAME."`.`{$tableName}_catalogue` ("
                    ."`id` INT NOT NULL AUTO_INCREMENT,"
                    .$addignColumns
                    ."`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,"
                    ."`active` BOOLEAN DEFAULT TRUE,"
                    ."PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8";

        //echo "<br /> queryToCreateTable: ".$queryToCreateTable."<br />";
        return ["createSentence"=>$queryToCreateTable,"indexSentences"=>self::$indexKeys,"foreignSentences"=>self::$foreignKeys];
    }

    public static function setAlterByForeignSentences(string $tableName, string $columnName, string $referencedTableName, string $refencedColumnName) : void {
        array_push(self::$foreignKeys,self::getForeignSentence($tableName,$columnName,$referencedTableName,$refencedColumnName));
        array_push(self::$indexKeys,self::getIndexSentence($tableName,$columnName,true));

    }

    public static function getForeignSentence(string $tableName, string $columnName, string $referencedTableName, string $refencedColumnName) : string {
        $constraintName ="fk_".explode("_",$referencedTableName)[0]."_".explode("_",$tableName)[0];
        return "ALTER TABLE `".DB_NAME."`.`{$tableName}_catalogue`"
            ." ADD CONSTRAINT `{$constraintName}`"
            ." FOREIGN KEY ({$columnName})"
            ." REFERENCES `{$referencedTableName}_catalogue`({$refencedColumnName});";
    }

    public static function getIndexSentence(string $tableName, string $columnName, bool $isForeign = false) : string {
        $indexName = ($isForeign)?"fk_{$columnName}_idx":"st_{$columnName}_idx";
        return "ALTER TABLE `".DB_NAME."`.`{$tableName}_catalogue`"
            ." ADD INDEX `{$indexName}` (`{$columnName}` ASC);";

    }
}
?>