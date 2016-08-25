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
        // $result = $this->grant_array($code, $message, $data);
        return json_encode($result);
    }
    
    function getBalance($balance){
        if(isset($balance['username'])){
            $message = "success";
            $code = "22000";
        } else {
            $message = "error";
            $code = "440";
        }
            $data = array("username"=>$balance['name'],"balance"=>$balance['balance']);
            $result = array(
                'code' => $code,
                'message' => $message,
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
    $result = Response::getBalance($balance);
    echo $result;
}