<?php
namespace App\Controllers;

class UploadEdiFile {
    
    public static int $initEnsembleLine=0;
    public static int $endEnsembleLine;
    public static int $currentEnsembleLine;
    public static int $currentEnsembleColumn=0;
    public static int $ensembleLevels=0;
    public static array $currentEnsembleRow = [ "rept"=>0,"reiterate" => true,"numSegments" => 0,"segments" => [0=>["level"=>0,"parentLevel"=>0,"content"=>"comntemnt","rept"=>0,"reiterate"=>true,"numSegments"=>0,"segments"=>[]]]];
    public static array  $currentEnsambledEdi = array();

    public static function readFile(string $targetDir){
        
        $allowedFileTypes = array("edi" => "application/octet-stream","x12" => "application/octet-stream");
        $fileName  = basename($_FILES["ediToUpload"]["name"]);
        $nameLength = strlen($fileName);
        $ext = strtolower(substr($fileName,$nameLength-4,$nameLength));
        $targetPath = $targetDir."/edi/[code]/transsaction_[code]_".time().$ext;
        $fileType = $_FILES["ediToUpload"]["type"];
        var_dump("info: [ ".date("Y-m-d H:i:s")." ] file properties: ",$fileName,$targetPath,$fileType);

        if(in_array($fileType, $allowedFileTypes)){
            // Check whether file exists before uploading it
            echo "test csv ok to save in: ".$targetPath;
            if(file_exists($targetPath)){
                echo $fileName . " is already exists.";
            } 
            else {
                $data = 1;
                if (($handle = \file_get_contents($_FILES["ediToUpload"]["tmp_name"], "r")) !== FALSE) {

                    $columns = [];
                    $rows = [];
                    $i = 0;
                    //var_dump("info: [ ".date("Y-m-d H:i:s")." ] file data: ",$handle);
                    //$secuenceToEnsamble = ["~",'**',"*"];
                    $secuenceToEnsamble = ["~","*",":"];
                    self::$ensembleLevels = count($secuenceToEnsamble);

                    //First segmentation
                    self::getRecursiveRowsAndColumns($secuenceToEnsamble,0, $handle);
                    echo "<br />";
                    var_dump("final structure: ",self::$currentEnsambledEdi); 
                    echo "<br /> must to safe File <br />";
                    /*                 if(move_uploaded_file($_FILES["csvToUpload"]["tmp_name"], $targetPath)) echo "Se guardo correctamente. ";
                                else echo "No se puede guardar el archivo. "; */
                }
            }
        } 
    }

    protected static function getRecursiveRowsAndColumns(array $baseToEnsemble, int $level, string $handleData, int $parentLevel = -1, $parentNode = -1){
        
        echo " <br /> Parent Level: {$parentLevel}";     

        $elementsEnsemble = explode($baseToEnsemble[$level], $handleData);
        $resultElements = count($elementsEnsemble);
        
        if($resultElements > 0){
            self::recursivelyRetrieve($level, $parentLevel, $baseToEnsemble, $handleData, $resultElements, $elementsEnsemble, $parentNode);
        }
   }

    protected static function recursivelyRetrieve(int $level, int $parentLevel, array $baseToEnsemble, string $handleData, int $resultElements, array $elementsEnsemble, int $parentNode) {

        self::setCurrentEnsembreRow($level, $parentLevel, $baseToEnsemble, $handleData, $resultElements);

        $i = 0;
        foreach($elementsEnsemble as $rowToEnsemble){
            $rowToEnsemble = str_replace(" ", "", $rowToEnsemble);

            self::setCurrentLineOrColumn($level, $i, $rowToEnsemble, $baseToEnsemble);

            ++$i;
            $toEvalSingleElement = self::lookForRecursiveCall($baseToEnsemble, $rowToEnsemble, $level);

                //var_dump(str_contains($baseToEnsemble[$level+1], $rowToEnsemble),str_contains($baseToEnsemble[$level+2], $rowToEnsemble));

            if($toEvalSingleElement)
                self::setValuesForSecuencedSchema($parentLevel, $parentNode, $handleData, $resultElements, $level, $rowToEnsemble);

        }
        if($parentLevel === 0){
            //var_dump(" last form to Row: ",$this->currentEnsembleRow["segments"]);
            array_push(self::$currentEnsambledEdi,self::$currentEnsembleRow);
        }
    }

    protected static function setCurrentEnsembreRow(int $level, int $parentLevel, array $baseToEnsemble, string $handleData, int $resultElements){
        if($level > 0 && $parentLevel === 0){
            echo " <br /> 1 Ensamble level {$baseToEnsemble[$level]} and hanlde Data: {$handleData}";                
            self::$currentEnsembleRow = ["rept"=>self::$currentEnsembleLine,"reiterate" => true, "numSegments" => $resultElements,"segments" =>array(),"content"=>$handleData];

        }
        else if($level > 0){
            echo " <br /> 2+ Ensamble level {$baseToEnsemble[$level]} and hanlde Data: {$handleData}";
            
        }
        else{
            echo " <br /> 0 Ensamble level {$baseToEnsemble[$level]} is the begin of recursive funct";
            self::$endEnsembleLine = $resultElements;
        }
    }

    protected static function setCurrentLineOrColumn(int $level, int $i, string $rowToEnsemble, array $baseToEnsemble){
        if($level === 0){
            self::$currentEnsembleLine = $i;
            echo "<br /> Row number [ ".($i+1)." ] Data: ".$rowToEnsemble;
        }
        else{
            self::$currentEnsembleColumn = $i;
            echo " <br /> Columna number [ ".($i+1)." ] type [".$baseToEnsemble[$level]."] Data: ".$rowToEnsemble;
        }
    }

    protected static function lookForRecursiveCall(array $baseToEnsemble, string $rowToEnsemble, int $level) : bool {

        $incrementLevel = $level+1;
        $toEvalSingleElement = false;
        if($incrementLevel < self::$ensembleLevels){
            for ($n=$incrementLevel; $n < self::$ensembleLevels; $n++) {
                
                if(isset($baseToEnsemble[$n])){
                    echo "<br /> Ensamble level to eval: {$baseToEnsemble[$n]} ";
                    if(str_contains($rowToEnsemble, $baseToEnsemble[$n])){
                        self::getRecursiveRowsAndColumns($baseToEnsemble,$incrementLevel, $rowToEnsemble,$level,self::$currentEnsembleColumn);
                        $n = self::$ensembleLevels;
                    } else if( $n+1 === self::$ensembleLevels){
                        $toEvalSingleElement = true;

                    }
                    
                }
            }
        } else 
            $toEvalSingleElement = true;
        
        return $toEvalSingleElement;
    }

    protected static function setValuesForSecuencedSchema(int $parentLevel, int $parentNode, string $handleData, int $resultElements, int $level, string $rowToEnsemble){
        if($parentLevel > 0 && $parentNode > -1){
            if(!isset(self::$currentEnsembleRow["segments"][$parentNode]))
                self::$currentEnsembleRow["segments"][$parentNode] = ["level"=>$parentLevel,"parentLevel"=>$parentLevel-1,"content"=>$handleData,"rept"=>$parentNode,"numSegments"=>$resultElements, "segments" => array()];
            
            $segmentToAdd = ["level"=>$level,"parentLevel"=>$parentLevel,"content"=>$rowToEnsemble,"rept"=>self::$currentEnsembleColumn,"numSegments"=>0,"parentNode"=>$parentNode];
            array_push(self::$currentEnsembleRow["segments"][$parentNode]["segments"], $segmentToAdd);
    
        }else {
            $segmentToAdd = ["level"=>$level,"parentLevel"=>$level-1,"content"=>$rowToEnsemble,"rept"=>self::$currentEnsembleColumn,"numSegments"=>0];
            array_push(self::$currentEnsembleRow["segments"], $segmentToAdd);

        }
    }
}


?>