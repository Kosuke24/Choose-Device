<?php
session_start();
// MySQL接続情報
$host     = 'localhost';
$db_username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

// エラーモード用配列
$err_msg = array();
//イメージディレクトリ変数
$img_dir = './img/';
//レビュー送信された時のリザルト変数
$result_msg = '';
//GETで商品IDが送られてきたか確認する
if(isset($_GET['item_id']) === TRUE){
  $item_id = $_GET['item_id'];
}else{
    header('Location:item_review.php');
  }

//セッション変数からユーザー名を取得する
if(isset($_SESSION['user_name']) === TRUE){
  $user_name = $_SESSION['user_name'];
}else{
  header('Location:login.php');
  exit;
}
// var_dump($_POST['review']);
//レビューが空白で送られてきたときの処理(後で追記)

//DB接続
try{
  $dbh = new PDO($dsn,$db_username,$db_password);
  //エラーモードの設定とエミュレーションの設定
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//以下、商品の画像と商品名のデータを取得する(画面に表示するため)---------------------------------
  $sql = 'SELECT name,img
          FROM ec_item_master
          WHERE item_id = ?';
  //実行準備
  $stmt = $dbh->prepare($sql);
  // バインド
  $stmt->bindValue(1,$item_id,PDO::PARAM_INT);
  // 実行
  $stmt->execute();
  // 配列に格納
  $row = $stmt->fetchAll();
  // var_dump($row);
  //htmlspecialcharsを適用する
  $data['name'] = htmlspecialchars($row[0]['name'],ENT_QUOTES,'UTF-8');
  $data['img'] = htmlspecialchars($row[0]['img'],ENT_QUOTES,'UTF-8');
  // var_dump($data);
  // 以下、レビュー送信ぼたが押された時の処理-----------------------------------------------
  if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review']) === TRUE){
    if($_POST['review'] !== ""){
      $review = $_POST['review'];
      try{
        //クエリを作成
        $sql = 'INSERT INTO ec_item_review (item_id,review,create_datetime)
                VALUES(?,?,now())';
        // 実行準備
        $stmt = $dbh->prepare($sql);
        // バインド
        $stmt->bindValue(1,$item_id,PDO::PARAM_INT);
        $stmt->bindValue(2,$review,PDO::PARAM_STR);
        // 実行
        $stmt->execute();
        $result_msg = 'ご協力ありがとうございました！' ;
      }catch(PDOException $e){
        throw $e;
      }
    }else{
      $err_msg[] = 'レビューが不正な値か空白です。再度レビューをお書き下さい';
    }
  }
}catch(PDOException $e){
  $err_msg[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title>商品一覧</title>
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
    <link rel="stylesheet" href="./css/review_write.css" />
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
        <div class="items">
          <div class="item">
            <img src="<?php print $img_dir . $data['img'];?>">
          </div>
          <div class="item">
            <form method="post">
              <textarea placeholder="ここにレビューを記入してください" name="review"></textarea>
              <input type="submit" value="レビューを送信" />
            </form>
          </div>
        </div>
        <!-- レビュー送信ボタンが押された時に表示 -->
        <p class="item_name"><?php print $data['name'];?></p>
        <?php if(empty($result_msg) !==TRUE){ ?>
        <p class="result"><?php print $result_msg;?></p>
        <?php } ?>
        <?php if(count($err_msg) > 0){
                foreach($err_msg as $value){ ?>
        <p class="result">
          <?php print $value;?>
        </p>
        <?php }
            } ?>
        <p class="question">このデバイスは以下のどのタイプですか？</p>
           <form class="radio" method="post"> 
               <input type="radio" name="select" value="" id="function"/ />
               <label for="function">機能重視</label>
               <input type="radio" name="select" value="" id="cost"/>
               <label for="cost">コスパ重視</label>
               <input type="radio" name="select" value="" id="design"/>
           <label for="design">デザイン重視</label>
           </form>
        <p class="move"><a href="item_top.php">商品一覧ページへ戻る</a></p>
    </main>
    <footer>
      <section class="news">
        <p>NEWS</p>
        <!--hrefには外部リンク-->
        <a href="">MORE</a>
      </section>

      <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>