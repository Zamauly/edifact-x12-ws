<?php
namespace App\Models;

use App\Interfaces\ModelInterface;

class FileModel implements ModelInterface
{

    protected int $id;
    protected string $fileName;
    protected string $fileType;
    protected string $content;
    protected string $createdAt;
    protected bool $active;

    public function __construct(int $id = 0,string $fileName = "", string $fileType = "", string $content = "",string $createdAt = "",bool $active = true) { 
        $this->id = $id;
        $this->fileName = $fileName;
        $this->fileType = $fileType;
        $this->content = $content;
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

    public function setContent(string $content){
        $this->content = $content;
    }

    public function getContent() : string {
        return $this->content;
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
            ."\", content: \"".$this->content."\", createdAt: \"".$this->createdAt."\", active: \"".$this->active."\", ] ";
    }

    // CRUD OPERATIONS
    public function create(array $data = [])
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
