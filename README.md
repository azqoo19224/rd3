# rd3
Api作業


/////////////////////////////////////////////////////////////////////////////////////

addUser method

參數1 (string)username

範例:https://azqooo-azqoo19224.c9users.io/Api/Home.php/addUser?username=Rain


/////////////////////////////////////////////////////////////////////////////////////

getBalance method

參數1 (string)username

範例:https://azqooo-azqoo19224.c9users.io/Api/Home.php/getBalance?username=Adam

**如果使用者重複   會顯示錯誤訊息

/////////////////////////////////////////////////////////////////////////////////////

updateBalance method

參數1 (string)username
參數2 (IN,OUT) type
參數3 (int)amount

範例:https://azqooo-azqoo19224.c9users.io/Api/Home.php/updateBalance?username=Adam&type=IN&amount=2000

**轉帳成功會顯示該次交易的版本號  checkTransfer API 可以利用版本號+使用者名稱  查詢該筆交易

/////////////////////////////////////////////////////////////////////////////////////

checkTransfer metod

參數1 (string)username
參數2 (int)version

範例:https://azqooo-azqoo19224.c9users.io/Api/Home.php/checkTransfer?username=Adam&version=7

**如果版本號不確定  請先隨機輸入   錯誤訊息會顯示現有的版本號