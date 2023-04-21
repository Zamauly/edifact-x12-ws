<?php
namespace App\Models;

use App\Interfaces\ModelInterface;
//require(APP_ROOT."/Config/initConfig.php");
use App\Config\DbConfig;
use Exception;

class UserModel implements ModelInterface
{

    protected int $id;
    protected string $userName;
    protected string $pswEnc;
    protected string $createdAt;
    protected bool $active;
    protected DbConfig $dbConfig;

    public function __construct(int $id = 0,string $userName = null, string $pswEnc = null, string $createdAt = null,bool $active = true) { 
        $this->id = $id;
        $this->userName = $userName;
        $this->pswEnc = $pswEnc;
        $this->createdAt = $createdAt;
        $this->active = $active;
    }
    
    public function setId(int $id){
        $this->id = $id;
    }

    public function getId() : int{
        return $this->id;
    }

    public function setUserName(string $userName){
        $this->userName = $userName;
    }

    public function getUserName() : string {
        return $this->userName;
    }

    public function setPswEnc(string $pswEnc){
        $this->pswEnc = $pswEnc;
    }

    public function getPswEnc() : string {
        return $this->pswEnc ?? "";
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
        return " UserModel: [ id: \"".$this->id."\", userName: \"".$this->userName."\", pswEnc: \"".$this->pswEnc
            ."\", createdAt: \"".$this->createdAt."\", active: \"".$this->active."\", ] ";
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

    public function readByName(string $userName = null) : UserModel
    {
        if(!empty($userName) && $userName !== null){
            $link = null;
            try{
                $this->dbConfig = new DbConfig();
                $link = $this->dbConfig->openConnect();
                trim($userName);
                $query = "SELECT * FROM `users` WHERE user_name='$userName' and active = true";
    
                //var_dump("info: [ ".date("Y-m-d H:i:s")." ] read Name: ".$userName,$query);
                $result = mysqli_query($link, $query);

                if(mysqli_num_rows($result)>0){     
                    //var_dump("info: [ ".date("Y-m-d H:i:s")." found by Name: ",$result);   
                    while ($row = $result->fetch_assoc()) {
                        $this->id = $row["id"];
                        $this->userName = $row["user_name"];
                        $this->pswEnc = $row["psw_enc"];
                        $this->createdAt = $row["created_at"];
                        $this->active = $row["active"];
                        //printf ("%s (%s)\n", $row["user_name"], $row["psw_enc"]);
                    }
                    //echo json_encode(array('success'=>1));
                    $result->free();
                } else {
                    //var_dump("info: [ ".date("Y-m-d H:i:s")." not found by Name: ",$result);
                    //echo json_encode(array('success'=>0));
                }
            
            }catch(Exception $err){
                var_dump("error: [ ".date("Y-m-d H:i:s")." ] at [ UserModel.readByName ] : ".$err->getMessage());
                
            }finally{
                if(!empty($link) && $link !== null)
                    $this->dbConfig->closeConnect($link);
            }

        } else
            var_dump("error: [ ".date("Y-m-d H:i:s")." ] read Name its null: ");
        
        return $this;
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
