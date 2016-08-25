<?php
header("Countent-Type;text/html; charset=utf-8");
require_once "DB.php";

DB::pdoConnect();

$url = $_SERVER['REQUEST_URI'];
$url = explode('/', $url);
$url = explode('?', $url[3]);
$api = $url[0];

class Response{
    function formatError(){
        $message = "format Error";
        $data = array("code" => "280",'message' => $message);
        $result = array("result"=>false,
            'data' => $data
        );
        
        return json_encode($result);
        
    }
    
    function getUser() {
        $message = "success";
        $data = array("username"=>$_GET['username']);
        $code = "4000";
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        return json_encode($result);
    }
    
    function getBalance($balance){
        if($balance['name'] != null){
            $message = "success";
            $code = "4400";
        } else {
            $message = "error";
            $code = "200";
        }

        $data = array("code" => $code,'message' => $message,"username"=>$balance['name'],"balance"=>$balance['balance']);
        $result = array("result"=>true,
            'data' => $data
        );
        
        return json_encode($result);
        }
    
  
    function getUpdateOutError() {
        $data = array("code" => "220",'message' => "Out error Insufficient");
        $result = array("result"=>false,
            'data' => $data
        );

        return json_encode($result);
    }
    
     function getUpdateError() {
        $data = array("code" => "240",'message' => "timeout");
        $result = array("result"=>false,
            'data' => $data
        );

        return json_encode($result);
    }
    
    function getUpdateSuccess() {
        $data = array("code" => "4200",'message' => "success", "type" => $_GET["type"], "username" => $_GET["username"], "amount" => $_GET["amount"]);
        $result = array("result"=>true,
            'data' => $data
        );

        return json_encode($result);
    }
}

class addUser{
    function addUser(){
        $balance = 10000;
        $version = 0;
        $insertA = DB::$db->prepare("INSERT `dataA` (`name`, `balance`, `version`) VALUES (?,?,?)");
        $insertA->bindParam(1, $_GET['username']);
        $insertA->bindParam(2, $balance);
        $insertA->bindParam(3, $version);
        $insertA->execute();
        $insertB = DB::$db->prepare("INSERT `dataB` (`name`, `balance`, `version`) VALUES (?,?,?)");
        $insertB->bindParam(1, $_GET['username']);
        $insertB->bindParam(2, $balance);
        $insertB->bindParam(3, $version);
        $insertB->execute();
    }
}

class getBalance{
     function getBalance(){
        $searchUser = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        $searchUser->bindParam(1, $_GET['username']);
        $searchUser->execute();
        $balance = $searchUser->fetch();
        return $balance;
    }
    function getUserBalance(){
        $searchUser = DB::$db->prepare("SELECT * FROM `dataB` WHERE `name` = ?");
        $searchUser->bindParam(1, $_GET['username']);
        $searchUser->execute();
        $balance = $searchUser->fetch();
        return $balance;
    }
}

class updateBalance{
    function getUpdate(){
        $Response = new Response;
        $searchUserA = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        $searchUserA->bindParam(1, $_GET['username']);
        $searchUserA->execute();
        $userA = $searchUserA->fetch();
        
        if($_GET['type'] == "IN") {
            $balance = $userA["balance"] + $_GET["amount"];
        } else {
            if(($userA["balance"] - $_GET["amount"]) >= 0 && $userA["balance"] >=0) {
                $balance = $userA["balance"] - $_GET["amount"];
            } else {
                return $Response->getUpdateOutError($_GET['type']);
            }
            
        }
        
        // $searchUserB = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        // $searchUserB->bindParam(1, $_GET['username']);
        // $searchUserB->execute();
        // $userB = $searchUserB->fetch();
        // var_dump($userA);

    
        $updateA = DB::$db->prepare("UPDATE `dataA` SET `balance` = :balance ,`version` = `version` + 1 WHERE `name` = :name AND `version` = :version");
        $updateA->bindParam(":balance", $balance);
        $updateA->bindParam(":name", $_GET['username']);
        $updateA->bindParam(":version", $userA["version"]);
        $updateA->execute();

        if ($updateA->rowCount()) {
            return $Response->getUpdateSuccess();
        } else {
            return $Response->getUpdateError();
        }
        
        // $updateB = DB::$db->prepare("UPDATE `dataA` `balance` = ?, `version` = ? WHERE `name` = ?");
        // $updateB->bindParam(1, $balance);
        // $updateB->bindParam(2, $userB["version"]++);
        // $updateB->bindParam(3, $_GET['username']);
        // $updateB->bindParam(4, $userB["version"]);
        // $updateB->execute();
    }
}

$Response = new Response;

if($api == "addUser") {
    {
        if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username'])){
            $result = $Response->formatError();
            echo $result;
        } else {
            $addUser = new addUser;
            $addUser->addUser();
            $result = $Response->getUser();
            echo $result;
        }

        
    }
}

if($api == "getBalance" || $api == "getUserBalance") {
    $getBalance = new getBalance;
    
    if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username'])) {
        $result = $Response->formatError();
        
        echo $result;
    } else {
        if($api == "getBalance") {
            $balance = $getBalance->getBalance();
        } else {
            $balance = $getBalance->getUserBalance();
        }
    
        $result = $Response->getBalance($balance);
        echo $result;
    }
}

if($api == "updateBalance") {
    if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username']) && !preg_match("/^([0-9]+)$/",$_GET['amount']) && ($_GET['type'] != "IN" || $_GET["type"] != "OUT")) {
        $result = $Response->formatError();
        echo $result;
    } else {
        $updateBalance = new updateBalance;
        $result = $updateBalance->getUpdate();
        echo $result;
    }
}