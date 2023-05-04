<?php
namespace App\Controllers;

use App\Models\FileModel;
use App\Models\CsvModel;
use App\Interfaces\ControllerInterface;
use App\Utils\TablesUtil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class EdiFileController implements ControllerInterface{
    public $model;
    public $targetDir;
    public $initEnsembleLine;
    public $endEnsembleLine;
    public $currentEnsembleLine;
    public $currentEnsembleColumn;
    public $ensembleLevels;
    public array $columnReferencesStandartFields = ["table_field", "sheet_field", "type", "length", "is_primary", "unique_primary",	"is_foreign", "sheet_ref", "sheet_field_ref", "unique_foreign", "not_null", "is_index"];
    public array $columnReferencesPermitEmpty = ["is_index"];
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

            $allowedFileTypes = array("csv" => "text/csv","tsv" => "text/tsv", "xlsx"=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            $fileName  = basename($_FILES["csvToUpload"]["name"]);
            $targetPath = $this->targetDir."/worksheet/".$fileName;
            $fileType = $_FILES["csvToUpload"]["type"];
            //var_dump(time()." File type: ".$fileType);

            if(in_array($fileType, $allowedFileTypes)){
                // Check whether file exists before uploading it
                echo "test csv ok to save in: ".$targetPath;
                if(file_exists($targetPath)){
                    echo $fileName . " is already exists.";
                }
                else {
                    $data = 1;
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;

                    if (($spreadsheet = $reader->load($_FILES["csvToUpload"]["tmp_name"])) !== FALSE) {
                        echo "<br /> Could Open file: ".$fileName." <br />";
                        //var_dump($spreadsheet);
                        echo $spreadsheet->getSheetCount();
                        $i = 0;
                        $csvModel = new CsvModel();
                        $csvModel->setFileName($fileName);
                        $csvModel->setFileType($fileType);
                        $csvModel->setPath(explode("..", $targetPath)[1]);
                        //var_dump($spreadsheet->getSheetNames());
                        $sheetsList = $spreadsheet->getSheetNames();
                        $columnsSheet = "column_references";
                        if($indexColumnsSheet = array_search($columnsSheet, $sheetsList, true)){
                            echo "<br /> create tables by columns {$indexColumnsSheet}<br />";
                            $sheetToProcess = $spreadsheet->getSheetByName($sheetsList[$indexColumnsSheet])->toArray();
                            $dataToCreateTables = [];
                            $colummnsToCreateTables = [];
                            $currentTable = "";
                            foreach ($sheetToProcess as $currentElement => $elementValue) {
/*                                 echo "<br /> currentElement: ";
                                var_dump($currentElement);
                                echo "<br /> elementValue: ";
                                var_dump($elementValue); */
                                
                                if($currentElement>0){
                                    $processedDataTable = strtolower($elementValue[1]);
                                    if($currentElement === 1)
                                        $currentTable = $processedDataTable;
                                        //$dataToCreateTables[$currentTable];
                                    else if($currentElement > 1 && $currentTable!=="" && strcmp($currentTable,$processedDataTable)!==0)
                                        $currentTable = $processedDataTable;
                                    
                                    //echo "<br /> currentTable: <br />";var_dump($currentTable);
                                    unset($elementValue[1]);

                                    if(!isset($dataToCreateTables[$currentTable]))
                                        $dataToCreateTables[$currentTable] = [];
                                    
                                    array_push($dataToCreateTables[$currentTable],$elementValue);
                                } else{
                                    $fieldsErrors = "";
                                    $fieldsErrorsToStop = false;
                                    foreach($elementValue as $key => $fieldColumnToEval){
                                        $fieldColumnToEval = strtolower($fieldColumnToEval);
                                        if(!in_array($fieldColumnToEval, $this->columnReferencesStandartFields)){

                                            if(!in_array($fieldColumnToEval,$this->columnReferencesPermitEmpty))
                                                $fieldsErrorsToStop = true;
                                            
                                            $fieldsErrors .= " Error. Missing element {($key+1)} named: {$fieldColumnToEval} <br /> ";

                                        }

                                    }
                                    
                                    if($fieldsErrors !== ""){
                                        echo " <br />  Must to check your '{$columnsSheet}' sheet at next fields <br /> {$fieldsErrors}";
                                        if($fieldsErrorsToStop){
                                            echo " <br /> Stop Process. Required fields are missing!! <br />";
                                            return;
                                        }
                                            
                                    }
                                    
                                    unset($elementValue[1]);
                                    //$colummnsToCreateTables = $elementValue;
                                    //echo "<br /> Columns: <br />";var_dump($colummnsToCreateTables);
                                }
                                    
                                    
                                    //array_push($rows,$elementValue);
                            }
                            //echo "<br /> Data: <br />";var_dump($dataToCreateTables);
                            $tablesUtil = new TablesUtil();
                            $foreignKeysSentences = [];
                            $indexKeysSentences = [];
                            foreach($dataToCreateTables as $currentData => $dataValues){


                                $querysToDefineTab = $tablesUtil->especifyTabDeclare($currentData,$dataValues);

                                echo "<br /> Query To create table: {$querysToDefineTab["createSentence"]} <br />";
                                $csvModel->executeSentence($querysToDefineTab["createSentence"], null);

                                if(count($querysToDefineTab["indexSentences"])>0)
                                    foreach($querysToDefineTab["indexSentences"] as $indexSentence){
                                        echo "<br /> Query To alter table with index: {$indexSentence} <br />";
                                        $csvModel->executeSentence($indexSentence, null);

                                    }
                                
                                if(count($querysToDefineTab["foreignSentences"])>0)
                                    foreach($querysToDefineTab["foreignSentences"] as $foreignSentence)
                                        array_push($foreignKeysSentences,$foreignSentence);


                            }
                            foreach($foreignKeysSentences as $foreignSentence){
                                echo "<br /> Query To alter table with foreign: {$foreignSentence} <br />";
                                $csvModel->executeSentence($foreignSentence, null);

                            }

                            unset($sheetsList[$indexColumnsSheet]);
                        }
/*                         foreach ($sheetsList as $sheetKey => $sheetName) {
                            # code...
                            $columns = [];
                            $rows = [];
                            echo "<br /> sheetKey: ";
                            var_dump($sheetKey);
                            echo "<br /> sheetName: ";
                            var_dump($sheetName);
                            $currentSheet = $spreadsheet->getSheet($sheetKey)->toArray();
                            //unset($sheetData[0]);
                            foreach ($currentSheet as $currentElement => $elementValue) {
                                # code...
                                echo "<br /> currentElement: ";
                                var_dump($currentElement);
                                echo "<br /> elementValue: ";
                                var_dump($elementValue);
                                if($currentElement === 0)
                                    $columns = $elementValue;
                                else                                     
                                    array_push($rows,$elementValue);
                                
                            }
                            $data = [ "columnsToTab" => $columns, "rowsToTab" => $rows];
                            $csvModel->createDataStructure($data,$sheetName);
                        } */
/*                         while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
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
                        } */
                        
                        //fclose($handle);
                    }
/*                     if(move_uploaded_file($_FILES["csvToUpload"]["tmp_name"], $targetPath)) echo "Se guardo correctamente. ";
                    else echo "No se puede guardar el archivo. "; */
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