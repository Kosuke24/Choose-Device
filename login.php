<?php
// MySQL接続情報
$host     = 'localhost';
$username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//セッション
session_start();
if(isset($_SESSION['user_name']) === TRUE){
    header('Location:item_top.php');
    exit;
}
//ユーザーID情報を取得
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $name = htmlspecialchars($_POST['user_name'],ENT_QUOTES);
  $password = htmlspecialchars($_POST['password'],ENT_QUOTES);

  //DB接続
  try{
    $dbh = new PDO($dsn,$username,$db_password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    //クエリの作成
    $sql = 'SELECT user_id,user_name,password FROM ec_user WHERE user_name=? AND password=?';
    //実行準備
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,$name,PDO::PARAM_STR);
    $stmt->bindValue(2,$password,PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetchAll();
    if(count($row) === 1){
    $_SESSION['user_id'] = $row[0]['user_id'];
    $_SESSION['user_name'] = $row[0]['user_name'];
    $_SESSION['user_password'] = $row[0]['password'];
    header('Location:item_top.php');
    }else{
        $err_msg[] = 'ユーザー名かパスワードが異なります';
    }
  }catch(PDOException $e){
    print $e->getMessage();
  }
}
 ?>
<!DOCTYPE html>
<html lang="ja">
  <meta charset="utf-8" />
  <link rel="stylesheet" href="./css/login.css" />
  <link rel="stylesheet" href="./css/footer.css" />
  <title>ログイン画面</title>
  <body>
    <header>
      <img src="./logo/logo.png" />
    </header>
    <main>
      <section>
        <form action="" method="post">
          <div class="text">
            ユーザーID:<input type="text" name="user_name" placeholder="ユーザーID" />
          </div>
          <div class="text">
            パスワード:<input type="text" name="password" placeholder="パスワード" />
          </div>
          <div>
              <p>管理者ID:admin PW:admin</p>
          </div>
          <div>
            <input type="submit" value="ログイン" />
          </div>
        </form>
        <div class="register">
      <a href="user_register.php">登録はこちらから</a>
<?php if(isset($err_msg) === TRUE){ ?>
<?php foreach($err_msg as $value){ ?>
        <li><?php print $value;?></li>
<?php } ?>
<?php } ?>
        </div>
      </section>
    </main>
    <footer>
      <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>
  </body>
</html>
