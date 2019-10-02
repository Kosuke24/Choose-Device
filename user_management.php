<?php
// MySQL接続情報
$host     = 'localhost';
$username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//セッション確認
session_start();
if(isset($_SESSION['user_name']) === TRUE){
    $user_name = $_SESSION['user_name'];
}else{
    header('Location:login.php');
}
//ユーザーネームがadminでない場合は商品いちらんページへリダイレクト
if($user_name !== 'admin'){
    header('Location:item_top.php');
    exit;
}
//db接続
try{
  $dbh = new PDO($dsn,$username,$db_password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  //クエリを作成
  //user_id,user_name,create,datetimeを取得
  $sql = 'SELECT user_id,user_name,create_datetime FROM ec_user';
  //実行準備
  $stmt = $dbh->prepare($sql);
  //実行し配列として格納
  $stmt->execute();
  $row = $stmt->fetchAll();
  //htmlspecialcharsを適用する

  $i = 0;
  foreach ($row as $key => $value) {
    $data[$i]['user_id'] = htmlspecialchars($value['user_id'],ENT_QUOTES,'UTF-8');
    $data[$i]['user_name'] = htmlspecialchars($value['user_name'],ENT_QUOTES,'UTF-8');
    $data[$i]['time'] = htmlspecialchars($value['create_datetime'],ENT_QUOTES,'UTF-8');
    $i++;
  }
}catch(PDOException $e){
  print $e->getMessage();
}
 ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title>ユーザ管理画面</title>
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
    <link rel="stylesheet" href="./css/user_management.css" />
  </head>
  <body>
    <header>
      <div class="header_top">
        <p>ようこそ<span><?php print $user_name;?></span>さん</p>
        <div class="header_right">
          <?php if($user_name === 'admin'){ ?>
          <a href="user_management.php">ユーザー管理<img src="./img/human.jpg" /></a>
          <a href="item_management.php">商品管理<img src="./img/pencil.png" /></a>
          <?php } ?>
          <a href="cart.php">カート<img src="./img/cart.png" /></a>
          <a href="login.php">ログイン<img src="./img/login.jpg" /></a>
          <a href="logout.php">ログアウト<img src="./img/logout.jpg" /></a>        </div>
        <div class="header_bottom">
          <!--hrefはそれぞれのリンク先にする-->
          <a href="item_list.php">おすすめ商品</a>
          <a href="item_top.php">商品一覧</a>
          <a href="item_review.php">レビュー</a>
        </div>
    </div>
    <div class="logo">
      <img src="./logo/logo.png" />
    </div>
    </header>
    <main>
      <p>ユーザー情報一覧</p>
      <div class="user">
        <table>
          <tr>
            <th>ユーザーID</th>
            <th>名前</th>
            <th>登録日時</th>
          </tr>
          <?php foreach($data as $value){ ?>
          <tr>
            <td><?php print $value['user_id']; ?></td>
            <td><?php print $value['user_name']; ?></td>
            <td><?php print $value['time']; ?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    </main>
    <footer>
      <section class="news">
        <p>NEWS</p>
        <!--hrefには外部リンク-->
        <a href="">MORE</a>
      </section>
      <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>
