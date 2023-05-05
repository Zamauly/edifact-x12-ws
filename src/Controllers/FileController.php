<?php
namespace App\Controllers;

use App\Models\FileModel;
use App\Models\CsvModel;
use App\Interfaces\ControllerInterface;
use App\Controllers\UploadDataSheets;
use App\Controllers\UploadEdiFile;
use App\Utils\TablesUtil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class FileController implements ControllerInterface{
    public $model;
    public $targetDir;

    public function __construct() {
        $this->model = new FileModel();
        $this->targetDir = APP_ROOT."/../resources/uploads";
    }

    public function invoke() {
        require_once APP_ROOT."/Views/Afterlogin.php";
        $this->uploadFile();
    }

    public function uploadFile() {
        
        if(isset($_POST["submit-csv-file"])&&isset($_FILES["csvToUpload"])&& $_FILES["csvToUpload"]["error"] == 0){
            
            UploadDataSheets::readFile($this->targetDir);
            

        }
        else if(isset($_POST["submit-edi-file"])&&isset($_FILES["ediToUpload"])&& $_FILES["ediToUpload"]["error"] == 0){
            
            UploadEdiFile::readFile($this->targetDir);
        } 
    }
    
}