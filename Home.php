<?php
header("Countent-Type;text/html; charset=utf-8");

require_once "DB.php";

DB::pdoConnect();

$url = $_SERVER['REQUEST_URI'];
$url = explode('/', $url);
$url = explode('?', $url[3]);
$api = $url[0];

class Response{
    function formatError() {
        $message = "format Error";
        $data = array("code" => "280",'message' => $message);
        $result = array("result"=>false,
            'data' => $data
        );
        
        return json_encode($result);
        
    }
    
    function getaddUserSuccess() {
        $data = array("code" => "4100","username"=>$_GET['username'], 'message' => "add Success");
        $result = array("result"=>true,
            'data' => $data
        );

        return json_encode($result);
    }
    
    function getaddUserError() {
        $data = array("code" => "210","username"=>$_GET['username'], 'message' => "User repeat error");
        $result = array("result"=>false,
            'data' => $data
        );

        return json_encode($result);
    }
    
    function getBalance($balance) {
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
    
    function getUpdateSuccess($version) {
        $data = array("code" => "4200",'message' => "success", "type" => $_GET["type"], "username" => $_GET["username"], "amount" => $_GET["amount"], "version" => $version);
        $result = array("result"=>true,
            'data' => $data
        );

        return json_encode($result);
    }
    
    function getCheckSuccess($userA) {
        $data = array("code" => "4900",'message' => "success", "info" => $userA["info"], "username" => $userA["nameA"]);
        $result = array("result"=>true,
            'data' => $data
        );

        return json_encode($result);
    }
    
    function getCheckError($user) {
        foreach($user as $k){
                $version .= "'".$k["version"]."'";
            }

        $data = array("code" => "250",'message' => "No Data Found","version"=>$version);
        $result = array("result"=>false,
            'data' => $data
        );
        return json_encode($result);
    }
}

class AddUser{
    function getAddUser() {
        $search = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        $search->bindParam(1, $_GET['username']);
        $search->execute();
        $user = $search->fetch();
   
        $Response1 = new Response;
        
        if($user["name"] == $_GET['username']) {
            return $Response1->getaddUserError();
        } else {
            $balance = 10000;
            $version = 0;
            $insertA = DB::$db->prepare("INSERT `dataA` (`name`, `balance`, `version`) VALUES (?,?,?)");
            $insertA->bindParam(1, $_GET['username']);
            $insertA->bindParam(2, $balance);
            $insertA->bindParam(3, $version);
            $insertA->execute();

            // $insertB = DB::$db->prepare("INSERT `dataB` (`name`, `balance`, `version`) VALUES (?,?,?)");
            // $insertB->bindParam(1, $_GET['username']);
            // $insertB->bindParam(2, $balance);
            // $insertB->bindParam(3, $version);
            // $insertB->execute();

            return $Response1->getaddUserSuccess();
        }
    }
}

class GetBalance{
     function getBalance(){
        $searchUser = DB::$db->prepare("SELECT * FROM `dataA` WHERE `name` = ?");
        $searchUser->bindParam(1, $_GET['username']);
        $searchUser->execute();
        $balance = $searchUser->fetch();
        return $balance;
    }
    // function getUserBalance(){
    //     $searchUser = DB::$db->prepare("SELECT * FROM `dataB` WHERE `name` = ?");
    //     $searchUser->bindParam(1, $_GET['username']);
    //     $searchUser->execute();
    //     $balance = $searchUser->fetch();
    //     return $balance;
    // }
}

class UpdateBalance{
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

        $updateA = DB::$db->prepare("UPDATE `dataA` SET `balance` = :balance ,`version` = `version` + 1 WHERE `name` = :name AND `version` = :version");
        $updateA->bindParam(":balance", $balance);
        $updateA->bindParam(":name", $_GET['username']);
        $updateA->bindParam(":version", $userA["version"]);
        $updateA->execute();
        //判斷udateA是否有執行
        if ($updateA->rowCount()) {
            $info = $_GET['type'].":".$_GET["amount"];
            $version = $userA["version"] + 1;
            $insertA = DB::$db->prepare("INSERT `info` (`nameA`, `info`, `version`) VALUES (?,?,?)");
            $insertA->bindParam(1, $_GET['username']);
            $insertA->bindParam(2, $info);
            $insertA->bindParam(3, $version);
            $insertA->execute();
            
            return $Response->getUpdateSuccess($version);
        } else {
            return $Response->getUpdateError();
        }
        
    }
}

class CheckTransfer{
    function getCheckTransfer() {
        $Response = new Response;
        
        $searchUserA = DB::$db->prepare("SELECT * FROM `info` WHERE `nameA` = ? AND `version` = ?");
        $searchUserA->bindParam(1, $_GET['username']);
        $searchUserA->bindParam(2, $_GET['version']);
        $searchUserA->execute();
        if ($searchUserA->rowCount()) {
            $userA = $searchUserA->fetch();
            return $Response->getCheckSuccess($userA);
        } else {
            $searchUser = DB::$db->prepare("SELECT * FROM `info` WHERE `nameA` = ?");
            $searchUser->bindParam(1, $_GET['username']);
            $searchUser->execute();
            $user = $searchUser->fetchAll();
            return $Response->getCheckError($user);
       }
    }
}

$Response = new Response;
//新增帳號
if($api == "addUser") {
    
    if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username'])){
        $result = $Response->formatError();
        echo $result;
    } else {
        $addUser = new AddUser;
        echo $result = $addUser->getAddUser();
    }
}
//查詢餘額
if($api == "getBalance" || $api == "getUserBalance") {
    $getBalance = new GetBalance;
    
    if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username'])) {
        $result = $Response->formatError();
        
        echo $result;
    } else {

        $balance = $getBalance->getBalance();
        $result = $Response->getBalance($balance);
        echo $result;
    }
}
//轉帳
if($api == "updateBalance") {
    if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username'], $value) || $_GET['amount'] < 0 || !($_GET['type'] === "IN" || $_GET["type"] === "OUT")) {
   
            $result = $Response->formatError();
            echo $result;
        
    } else {
        $updateBalance = new UpdateBalance;
        $result = $updateBalance->getUpdate();
        echo $result;
    }
}
//查詢
if($api == "checkTransfer") {
    if(!preg_match("/^([0-9a-zA-Z]+)$/",$_GET['username']) || !preg_match("/^([0-9]+)$/",$_GET['version'])){
            $result = $Response->formatError();
            echo $result;
    } else {
        $checkTransfer = new CheckTransfer;
        $result = $checkTransfer->getCheckTransfer();
        echo $result;
    }
}
