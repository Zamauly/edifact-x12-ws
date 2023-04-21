<?php
namespace App\Controllers;

use App\Models\FileModel;
use App\Models\CsvModel;
use App\Interfaces\ControllerInterface;

class EdiFileController implements ControllerInterface{
    public $model;
    public $targetDir;
    public $initEnsembleLine;
    public $endEnsembleLine;
    public $currentEnsembleLine;
    public $currentEnsembleColumn;
    public $ensembleLevels;
    public $currentEnsembleRow = [ "rept"=>0,"reiterate" => true,"numSegments" => 0,"segments" => [0=>["level"=>0,"parentLevel"=>0,"content"=>"comntemnt","rept"=>0,"reiterate"=>true,"numSegments"=>0,"segments"=>[]]]];
    public $currenEnsambledEdi = array();

    public function __construct() {
        $this->model = new FileModel();
        $this->targetDir = APP_ROOT."/../resources/uploads";
        $this->initEnsembleLine = 0;
    }

    public function invoke() {
        require_once APP_ROOT."/Views/Afterlogin.php";
        $this->readFile();
    }

    public function readFile() {
        
        if(isset($_POST["submit-csv-file"])&&isset($_FILES["csvToUpload"])&& $_FILES["csvToUpload"]["error"] == 0){

            $allowedFileTypes = array("csv" => "text/csv","tsv" => "text/tsv");
            $fileName  = basename($_FILES["csvToUpload"]["name"]);
            $targetPath = $this->targetDir."/csv/".$fileName;
            $fileType = $_FILES["csvToUpload"]["type"];

            if(in_array($fileType, $allowedFileTypes)){
                // Check whether file exists before uploading it
                echo "test csv ok to save in: ".$targetPath;
                if(file_exists($targetPath)){
                    echo $fileName . " is already exists.";
                }
                else {
                    $data = 1;
                    if (($handle = fopen($_FILES["csvToUpload"]["tmp_name"], "r")) !== FALSE) {
                        $columns = [];
                        $rows = [];
                        $i = 0;
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $num = count($data);
                            //echo "<p> $num fields in line: <br /></p>\n";
                            $row = [];
                            //$data++;
                            for ($c=0; $c < $num; $c++) {
                                echo $data[$c] . "<br />\n";
                                if($i === 0){
                                    array_push($columns,$data[$c]);
                                } else {
                                    
                                    array_push($row,$data[$c]);
                                }
                            }
                            if($i>=1){
                                array_push($rows,$row);
                            }
                            $i += 1;
                        }
                        $csvModel = new CsvModel();
                        $csvModel->setFileName($fileName);
                        $csvModel->setFileType($fileType);
                        $csvModel->setPath(explode("..", $targetPath)[1]);
                        $data = [ "columnsToTab" => $columns, "rowsToTab" => $rows];
                        $csvModel->createDataStructure($data);
                        
                        fclose($handle);
                    }
                    if(move_uploaded_file($_FILES["csvToUpload"]["tmp_name"], $targetPath)) echo "Se guardo correctamente. ";
                    else echo "No se puede guardar el archivo. ";
                }
            }
            //If not a valid MIME type
            else{
                echo "Invalid file format. ";
            }
            

        }
        else if(isset($_POST["submit-edi-file"])&&isset($_FILES["ediToUpload"])&& $_FILES["ediToUpload"]["error"] == 0){
            $allowedFileTypes = array("edi" => "application/octet-stream","x12" => "application/octet-stream");
            $fileName  = basename($_FILES["ediToUpload"]["name"]);
            $nameLength = strlen($fileName);
            $ext = strtolower(substr($fileName,$nameLength-4,$nameLength));
            $targetPath = $this->targetDir."/edi/[code]/transsaction_[code]_".time().$ext;
            $fileType = $_FILES["ediToUpload"]["type"];
            var_dump("info: [ ".date("Y-m-d H:i:s")." ] file properties: ",$fileName,$targetPath,$fileType);

            if(in_array($fileType, $allowedFileTypes)){
                // Check whether file exists before uploading it
                echo "test csv ok to save in: ".$targetPath;
/*                 if(file_exists($targetPath)){
                    echo $fileName . " is already exists.";
                } =
                else { */
                $data = 1;
                if (($handle = \file_get_contents($_FILES["ediToUpload"]["tmp_name"], "r")) !== FALSE) {

                    $columns = [];
                    $rows = [];
                    $i = 0;
                    //var_dump("info: [ ".date("Y-m-d H:i:s")." ] file data: ",$handle);
                    //$secuenceToEnsamble = ["~",'**',"*"];
                    $secuenceToEnsamble = ["~","*",":"];
                    $this->ensembleLevels = count($secuenceToEnsamble);

                    //First segmentation
                    $this->getRecursiveRowsAndColumns($secuenceToEnsamble,0, $handle);
                    var_dump("final structure: ",$this->currenEnsambledEdi);           }
/*                 if(move_uploaded_file($_FILES["csvToUpload"]["tmp_name"], $targetPath)) echo "Se guardo correctamente. ";
                else echo "No se puede guardar el archivo. "; */
            }            
        } 
    }
    
    protected function getRecursiveRowsAndColumns(array $baseToEnsemble, int $level, string $handleData, int $parentLevel = -1, $parentNode = -1){
        
        echo " <br /> Parent Level: {$parentLevel}";     

        $elementsEnsemble = explode($baseToEnsemble[$level], $handleData);
        $resultElements = count($elementsEnsemble);
        
        if($resultElements > 0){
            if($level > 0 && $parentLevel === 0){
                echo " <br /> 1 Ensamble level {$baseToEnsemble[$level]} and hanlde Data: {$handleData}";                
                $this->currentEnsembleRow = ["rept"=>$this->currentEnsembleLine,"reiterate" => true, "numSegments" => $resultElements,"segments" =>array(),"content"=>$handleData];
    
            }
            else if($level > 0){
                echo " <br /> 2+ Ensamble level {$baseToEnsemble[$level]} and hanlde Data: {$handleData}";
                
            }
            else{
                echo " <br /> 0 Ensamble level {$baseToEnsemble[$level]} is the begin of recursive funct";
                $this->endEnsembleLine = $resultElements;
            }
            $i = 0;
            foreach($elementsEnsemble as $rowToEnsemble){
                $rowToEnsemble = str_replace(" ", "", $rowToEnsemble);
                if($level === 0){
                    $this->currentEnsembleLine = $i;
                    echo "<br /> Row number [ ".($i+1)." ] Data: ".$rowToEnsemble;
                }
                else{
                    $this->currentEnsembleColumn = $i;
                    echo " <br /> Columna number [ ".($i+1)." ] type [".$baseToEnsemble[$level]."] Data: ".$rowToEnsemble;
                }

                ++$i;
                $incrementLevel = $level+1;
                $toEvalSingleElement = false;
                if($incrementLevel < $this->ensembleLevels){
                    for ($n=$incrementLevel; $n < $this->ensembleLevels; $n++) {
                        
                        if(isset($baseToEnsemble[$n])){
                            echo "<br /> Ensamble level to eval: {$baseToEnsemble[$n]} ";
                            if(str_contains($rowToEnsemble, $baseToEnsemble[$n])){
                                $this->getRecursiveRowsAndColumns($baseToEnsemble,$incrementLevel, $rowToEnsemble,$level,$this->currentEnsembleColumn);
                                $n = $this->ensembleLevels;
                            } else if( $n+1 === $this->ensembleLevels){
                                $toEvalSingleElement = true;

                            }
                            
                        }
                    }
                } else 
                    $toEvalSingleElement = true;

                    //var_dump(str_contains($baseToEnsemble[$level+1], $rowToEnsemble),str_contains($baseToEnsemble[$level+2], $rowToEnsemble));

                if($toEvalSingleElement){
                    if($parentLevel > 0 && $parentNode > -1){
                        if(!isset($this->currentEnsembleRow["segments"][$parentNode]))
                            $this->currentEnsembleRow["segments"][$parentNode] = ["level"=>$parentLevel,"parentLevel"=>$parentLevel-1,"content"=>$handleData,"rept"=>$parentNode,"numSegments"=>$resultElements, "segments" => array()];
                        
                        $segmentToAdd = ["level"=>$level,"parentLevel"=>$parentLevel,"content"=>$rowToEnsemble,"rept"=>$this->currentEnsembleColumn,"numSegments"=>0,"parentNode"=>$parentNode];
                        array_push($this->currentEnsembleRow["segments"][$parentNode]["segments"], $segmentToAdd);
                
                    }else {
                        $segmentToAdd = ["level"=>$level,"parentLevel"=>$level-1,"content"=>$rowToEnsemble,"rept"=>$this->currentEnsembleColumn,"numSegments"=>0];
                        array_push($this->currentEnsembleRow["segments"], $segmentToAdd);

                    }

                }

            }
            if($parentLevel === 0){
                //var_dump(" last form to Row: ",$this->currentEnsembleRow["segments"]);
                array_push($this->currenEnsambledEdi,$this->currentEnsembleRow);
            }
        }
   }
}