<?php
//セッション開始
session_start();
//db接続情報
// MySQL接続情報
$host     = 'localhost';
$db_username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

//エラー配列用メッセージ
$err_msg = array();
//画像ディレクトリ
$img_dir = './img/';

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//セッション変数からユーザー情報を取得する
if(isset($_SESSION['user_name']) === TRUE){
    $user_name = $_SESSION['user_name'];
}

//GETでアイテムIDを取得する
if(isset($_GET['item_id']) === TRUE){
  $item_id = $_GET['item_id'];
}else{
  header('Location:item_top.php');
  exit;
}
// var_dump($item_id);
//DB接続
try{
  $dbh = new PDO($dsn,$db_username,$db_password);
  //エラーモードの設定とプリペアドステートメントをfalseにしとく
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  // クエリを作成商品イメージ、商品名、レビューを参照する
  $sql = 'SELECT ec_item_master.item_id,ec_item_master.name,ec_item_master.img,ec_item_master.price,
                 ec_item_review.review
          FROM ec_item_master INNER JOIN ec_item_review
          ON ec_item_master.item_id = ec_item_review.item_id
          WHERE ec_item_master.item_id = ?';
  //実行準備
  $stmt = $dbh->prepare($sql);
  // バインド
  $stmt->bindValue(1,$item_id,PDO::PARAM_INT);
  //実行
  $stmt->execute();
  // 配列に格納
  $row = $stmt->fetchAll();
//   var_dump($row);
  //htmlspecialcharsを適用する
  //レビューデータがある時、つまり$rowが空じゃないときにHTMLspecialcharsを適用する(後で追記)

    $i = 0;
    foreach($row as $value){
      $data[$i]['item_id'] = htmlspecialchars($value['item_id']);
      $data[$i]['name'] = htmlspecialchars($value['name']);
      $data[$i]['img'] = htmlspecialchars($value['img']);
      $data[$i]['review'] = htmlspecialchars($value['review']);
      $data[$i]['price'] = htmlspecialchars($value['price']);
      $i ++;
    }

  // var_dump($data);
}catch(PDOException $e){
  $err_msg[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title>商品レビュー</title>
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
    <link rel="stylesheet" href="./css/item_review.css" />
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
             <a href="logout.php">ログアウト<img src="./img/logout.jpg" /></a>
             </div>
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
      <?php if(isset($data) === TRUE){ ?>
      <div class="items">
        <div class="item">
          <img class="img" src="<?php print $img_dir . $data[0]['img'];?>" />
        </div>
        <div class="item">
          <ul>
            <?php
            //$rowが取れている時だけレビューを表示して、レビューデータがない($rowが空の場合)は「レビューがありません」と表示する。(後で追記)
              foreach($data as $value){ ?>
            <li>
                <?php print $value['review'];?>
            </li>
          <?php } ?>
          </ul>
        </div>
      </div>    
        <div class="item">
          <a href="review_write.php?item_id=<?php print $item_id; ?>">レビューを書く</a>
        </div>
      <p class="name"><?php print $data[0]['name'];?></p>
    <?php }else{ ?>
      <p class="no_review">この商品にはレビューが存在しません。商品一覧ページへお戻り下さいませ。</p>
      <div class="item">
          <a href="review_write.php?item_id=<?php print $item_id; ?>">レビューを書く</a>
        </div>
    <?php } ?>
      <p class="move"><a href="item_top.php">商品一覧ページへ戻る</a></p>
    </main>
    <footer>
      <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>
  </body>
</html>