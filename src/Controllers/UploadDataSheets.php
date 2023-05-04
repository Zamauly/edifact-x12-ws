<?php 
namespace App\Controllers;

use App\Models\CsvModel;
use App\Utils\TablesUtil;


class UploadDataSheets {

    public static array $columnReferencesStandartFields = ["table_field", "sheet_field", "type", "length", "is_primary", "unique_primary",	"is_foreign", "sheet_ref", "sheet_field_ref", "unique_foreign", "not_null", "is_index"];
    public static array $columnReferencesPermitEmpty = ["is_index"];

    public static function readFile(string $targetDir){
        $allowedFileTypes = array("csv" => "text/csv","tsv" => "text/tsv", "xlsx"=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        $fileName  = basename($_FILES["csvToUpload"]["name"]);
        $targetPath = $targetDir."/worksheet/".$fileName;
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
                                    if(!in_array($fieldColumnToEval, self::$columnReferencesStandartFields)){

                                        if(!in_array($fieldColumnToEval,self::$columnReferencesPermitEmpty))
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
                return true;
            }
        }
        //If not a valid MIME type
        else{
            echo "Invalid file format. ";
            return false;
        }
    }

    public static final function invoke() : void {
        if(isset($_POST["submit-csv-file"]))
            echo "test";
    }

}

?>