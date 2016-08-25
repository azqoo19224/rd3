<?php
require_once "DB.php";

DB::pdoConnect();

$url = $_SERVER['REQUEST_URI'];
$url = explode('/', $url);
$url = explode('?', $url[3]);
$api = $url[0];

class Response{
    function getUser() {
        $message = "success";
        $data = array("username"=>$_GET['username']);
        $code = "44000";
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
            $code = "22000";
        } else {
            $message = "error";
            $code = "440";
        }
            $result = $this->getReturn($code, $message, $data, $balance);
            return $result;
        }

    function getReturn($code, $message, $data, $balance){
        $data = array("code" => $code,'message' => $message,"username"=>$balance['name'],"balance"=>$balance['balance']);
        $result = array("result"=>"true",
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
    function update(){
        
        $searchUserA = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        $searchUserA->bindParam(1, $_GET['username']);
        $searchUserA->execute();
        $userA = $searchUserA->fetch();
        
        $searchUserB = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        $searchUserB->bindParam(1, $_GET['username']);
        $searchUserB->execute();
        $userB = $searchUserB->fetch();
        
        $updateA = DB::$db->prepare("UPDATE `dataA` `balance` = ?, `version` = ? WHERE `name` = ? AND WHERE `version` = ?");
        $updateA->bindParam(1, $balance);
        $updateA->bindParam(2, $userA["version"]++);
        $updateA->bindParam(3, $_GET['username']);
        $updateA->bindParam(4, $userA["version"]);
        $updateA->execute();
        
        $updateB = DB::$db->prepare("UPDATE `dataA` `balance` = ?, `version` = ? WHERE `name` = ?");
        $updateB->bindParam(1, $balance);
        $updateB->bindParam(2, $userB["version"]++);
        $updateB->bindParam(3, $_GET['username']);
        $updateB->bindParam(4, $userB["version"]);
        $updateB->execute();
        }
}


if($api == "addUser") {
    $addUser = new addUser;
    $addUser->addUser();
    $result = Response::getUser();
    echo $result;
}

if($api == "getBalance" || $api == "getUserBalance") {
    $getBalance = new getBalance;
    if($api == "getBalance") {
        $balance = $getBalance->getBalance();
    } else {
        $balance = $getBalance->getUserBalance();
    }
    $Response = new Response;
    $result = $Response->getBalance($balance);
    echo $result;
}

if($api == "updateBalance") {
    
    
    
}