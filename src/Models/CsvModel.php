<?php
namespace App\Models;

use App\Interfaces\ModelInterface;
use App\Config\DbConfig;
use Exception;
use mysqli_result;
use mysqli;

class CsvModel implements ModelInterface
{

    protected int $id;
    protected string $fileName;
    protected string $fileType;
    protected string $path;
    protected string $createdAt;
    protected bool $active;
    private DbConfig $dbConfig;
    private string $assosiatedTable = "uploaded_csv_files";
    private string $assosiatedColumns = "(file_name,file_type,path)";

    public function __construct(int $id = 0,string $fileName = "", string $fileType = "", string $path = "",string $createdAt = "",bool $active = true) { 
        $this->id = $id;
        $this->fileName = $fileName;
        $this->fileType = $fileType;
        $this->path = $path;
        $this->createdAt = $createdAt;
        $this->active = $active;
    }
    
    public function setId(int $id){
        $this->id = $id;
    }

    public function getId() : int{
        return $this->id;
    }

    public function setFileName(string $fileName){
        $this->fileName = $fileName;
    }

    public function getFileName() : string {
        return $this->fileName;
    }

    public function setFileType(string $fileType){
        $this->fileType = $fileType;
    }

    public function getFileType() : string {
        return $this->fileType;
    }

    public function setPath(string $path){
        $this->path = $path;
    }

    public function getPath() : string {
        return $this->path;
    }

    public function setCreatedAt(string $createdAt){
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt() : string {
        return $this->createdAt;
    }

    public function setActive(bool $active){
        $this->active = $active;
    }

    public function getActive() : bool {
        return $this->active;
    }

    public function __toString() : string {
        return " FileModel: [ id: \"".$this->id."\", fileName: \"".$this->fileName."\", fileType: \"".$this->fileType
            ."\", path: \"".$this->path."\", createdAt: \"".$this->createdAt."\", active: \"".$this->active."\", ] ";
    }

    public function validateTable(bool $validate, mysqli_result $result) : bool {
        while ($row = $result->fetch_assoc()) {
            //var_dump("info: [ ".date("Y-m-d H:i:s")." ] read row: \n",intval($row["COUNT(*)"], 10));
            if(isset($row["COUNT(*)"])&&intval($row["COUNT(*)"], 10) === 0){
                echo "crear tabla";
                $validate = true;
            } else echo "No se creara la tabla debido a que ya existe";
        };
        return $validate;
    }

    public function createTableIfDoesntExists(array $data, bool $validate, string $tableName, mysqli $link ) : string {
        $columnsToInsert = "(";
        if(isset($data["columnsToTab"])&&count($data["columnsToTab"])>0){
            $addignColumns = "";
            foreach($data["columnsToTab"] as $column){
                if ($validate)$addignColumns .= "`".strtolower($column)."` TEXT NOT NULL,";
                $columnsToInsert .= strtolower($column).",";
            }
            if (str_ends_with($columnsToInsert, ','))
                $columnsToInsert = substr_replace($columnsToInsert,")",-1);
                
            if($validate){
                $queryCreateTab = "CREATE TABLE `".DB_NAME."`.`".$tableName."_catalogue` ( "
                    ."`id` INT NOT NULL AUTO_INCREMENT,"
                    .$addignColumns
                    ."`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,"
                    ."`active` BOOLEAN DEFAULT TRUE,"
                    ."PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8";
                //var_dump("info: [ ".date("Y-m-d H:i:s")." ] query to create table: ".$queryCreateTab);
                $result = mysqli_query($link, $queryCreateTab);
                if($result)
                    echo "Se ha creado correctamente la tabla: {$tableName}";
                else 
                    echo "Error. Imposible crear la tabla: {$tableName}";
            }
        }
        return $columnsToInsert;
    }

    public function insertIfItsNecesary(array $data, string $columnsToInsert, string $tableName, mysqli $link ) : void {

        if(isset($data["rowsToTab"])){
            $totalRows = count($data["rowsToTab"]);
            if($totalRows>0&&str_ends_with($columnsToInsert, ')')){
                $i = 0;
                $addingRows = "";
                foreach($data["rowsToTab"] as $row){
                    $j = 0;
                    $totalRowColumns = count($row);
                    foreach($row as $valueToInsert){
                        ++$j;
                        if(str_contains($valueToInsert,"'"))
                            $valueToInsert = str_replace("'","\"",$valueToInsert);

                        if($j === 1)
                            $addingRows .= "('".$valueToInsert."',";
                        else if($j === $totalRowColumns)
                            $addingRows .= "'".$valueToInsert."'),";
                        else
                            $addingRows .= "'".$valueToInsert."',";

                    }
                    if(++$i === $totalRows)
                        $addingRows = (str_ends_with($addingRows, ','))?substr_replace($addingRows,";",-1):$addingRows;

                }
                $queryInsertTab = "INSERT INTO `".DB_NAME."`.`".$tableName."_catalogue` "
                    .$columnsToInsert
                    ." VALUES ".$addingRows;
                var_dump("info: [ ".date("Y-m-d H:i:s")." ] query to create table: ".$queryInsertTab);
                $result = mysqli_query($link, $queryInsertTab);
                if($result)
                    echo "Se han insertado correctamente en la tabla: {$tableName} => {$totalRows} filas";
                else {
                    echo "<br /> Error. Imposible insertar en la tabla: {$tableName} <br />";
                    var_dump(mysqli_error($link));
                    echo "<br />";
                }

            }
        }
    }
    // CRUD OPERATIONS
    public function createDataStructure(array $data = [],string $sheetName = "")
    {
        //var_dump("info: [ ".date("Y-m-d H:i:s")." ] create Data: \n",$data);
        try{
            $this->dbConfig = new DbConfig();
            $link = $this->dbConfig->openConnect();
            $tableName = strtolower($sheetName);
            $queryValidateTab = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '".DB_NAME."' AND table_name = '".$tableName."_catalogue'";

            var_dump("info: [ ".date("Y-m-d H:i:s")." ] validate table if exists: ".$queryValidateTab);
            $result = mysqli_query($link, $queryValidateTab);
            if(mysqli_num_rows($result)>0){     
                //var_dump("info: [ ".date("Y-m-d H:i:s")." found by Name: ",$result);
                $validate = $this->validateTable(false,$result);

                $result->free();

                $this->createByStructure($link);

                $columnsToInsert = $this->createTableIfDoesntExists($data,$validate,$tableName,$link);

                $this->insertIfItsNecesary($data,$columnsToInsert,$tableName,$link);

            }
        }catch(Exception $err){
            var_dump("error: [ ".date("Y-m-d H:i:s")." ] at [ CsvModel.create ] : ".$err->getMessage());
            
        }finally{
            if(!empty($link) && $link !== null)
                $this->dbConfig->closeConnect($link);

        }

    }

    public function getValuesToInsert() : string {
        return "('{$this->fileName}','{$this->fileType}','{$this->path}')";
    }
    public function createByStructure( mysqli $link )
    {

        //var_dump("info: [ ".date("Y-m-d H:i:s")." ] create Data: \n",$data);
        
        $queryInsertTab = "INSERT INTO `".DB_NAME."`.`".$this->assosiatedTable."` "
            .$this->assosiatedColumns
            ." VALUES ".$this->getValuesToInsert().";";
        //var_dump("info: [ ".date("Y-m-d H:i:s")." ] query to create table: ".$queryInsertTab);
        $result = mysqli_query($link, $queryInsertTab);
        if($result)
            echo "Se han insertado correctamente en la tabla: {$this->assosiatedTable} => 1 filas";
        else 
            echo "Error. Imposible insertar en la tabla: {$this->assosiatedTable}";
    }

    public function create( array $data = [])
    {
        var_dump("info: [ ".date("Y-m-d H:i:s")." ] create Data: \n".$data);
    }

    public function read(int $id = 0)
    {
        var_dump("info: [ ".date("Y-m-d H:i:s")." ] read Id: ".$id);
    }

    public function update(int $id = 0, array $data = [])
    {
        var_dump("info: [ ".date("Y-m-d H:i:s")." ] update Id: ".$id." Data: \n".$data);
    }

    public function delete(int $id = 0)
    {
        var_dump("info: [ ".date("Y-m-d H:i:s")." ] delete Id: ".$id);
    }    
}
