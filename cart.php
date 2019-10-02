<?php

// MySQL接続情報
$host     = 'localhost';
$db_username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$db_name   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

//エラーメッセージ用配列
$err_msg = array();

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$db_name.';host='.$host.';charset='.$charset;

//イメージファイルのディレクトリ
$img_dir = './img/';
session_start();
//ログインしていたらユーザー名情報と、user_id情報を取得
if(isset($_SESSION['user_name']) === TRUE){
  $user_name = $_SESSION['user_name'];
  $user_id = $_SESSION['user_id'];
}
// var_dump($user_id);
//変更ボタンまたは削除ボタンが押された時の処理
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['push_button']) === TRUE
   && isset($_POST['item_id']) === TRUE){

    $push_button = $_POST['push_button'];
    $item_id = $_POST['item_id'];
    // var_dump($push_button);
    var_dump($item_id);
}
// var_dump($_POST['amount']);

// db接続
try{
  $dbh = new PDO($dsn,$db_username,$db_password);
  //エラーモードの設定とプリペアドステートメントをfalseにしとく
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  //以下、商品数が変更された時の処理-------------------------------------
  if(isset($_POST['amount']) === TRUE && $push_button === 'change_stock'){
      //変更した個数をスペース処理
      $amount = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '',$_POST['amount']);
      $amount = trim($amount);
  //空文字で送られてきた時の処理
  if($amount === ''){
    $err_msg[] = '数量を入力して下さい';
  }elseif(preg_match('/\A\d+\z/',$amount) !== 1){
    $err_msg[] = '数量は0以上の整数で入力して下さい';
  }
  if(empty($err_msg) === TRUE){
      try{
        //クエリを作成
        $sql = 'UPDATE ec_cart SET amount = ?,update_datetime=now() WHERE user_id = ? AND item_id = ?';
        //実行準備
        $stmt = $dbh->prepare($sql);
        //バインド
        $stmt->bindValue(1,$amount,PDO::PARAM_INT);
        $stmt->bindValue(2,$user_id,PDO::PARAM_INT);
        $stmt->bindValue(3,$item_id,PDO::PARAM_INT);
        //実行
        $stmt->execute();
      }catch(PDOException $e){
        throw $e;
      }
    }
  }
//以下商品削除ボタンが押された時の処理---------------------------------------------------------------------------
  if(isset($push_button) === TRUE && $push_button === 'delete'){
    try{
      // クエリを作成
      $sql = 'DELETE FROM ec_cart
              WHERE user_id = ? AND item_id = ?';
      // 実行準備
      $stmt = $dbh->prepare($sql);
      // バインド
      $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
      $stmt->bindValue(2,$item_id,PDO::PARAM_INT);
      // 実行
      $stmt->execute();
    }catch(PDOException $e){
      throw $e;
    }
  }
//以下、画面に表示するデータの取得-------------------------------------------------------------------------------
  //sqlを作成
  //WHEREにuser_idをいれることで誰がカートにいれたのかがわかる。
  try{
    $sql = 'SELECT ec_cart.user_id,ec_item_master.item_id,
                   ec_item_master.name,
                   ec_item_master.price,ec_item_master.img,
                   ec_cart.amount
            FROM   ec_cart
            INNER JOIN ec_item_master
            ON ec_cart.item_id = ec_item_master.item_id
            WHERE ec_cart.user_id = ?';
    //実行準備
    $stmt = $dbh->prepare($sql);
    //バインド
    $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
    //実行
    $stmt->execute();
    //配列として取得
    $row = $stmt->fetchAll();
    // echo '<pre>';
    // var_dump($row);
    //htmlspecialcharsを適用
    $i = 0;
    foreach($row as $value){
      $data[$i]['item_id'] = htmlspecialchars($value['item_id']);
      $data[$i]['name'] = htmlspecialchars($value['name']);
      $data[$i]['price'] = htmlspecialchars($value['price']);
      $data[$i]['img'] = htmlspecialchars($value['img']);
      $data[$i]['amount'] = htmlspecialchars($value['amount']);
      $i ++;
    }
  }catch(PDOException $e){
    throw $e;
  }
}catch(PDOException $e){
  $err_msg[] = $e->getMessage();
}

// var_dump($data);



 ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title>カート</title>
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
    <link rel="stylesheet" href="./css/cart.css" />
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
      <!--カートに商品が入っている時の処理-->
      <?php if (isset($data) === TRUE){ ?>
      <table>
        <tr class="head">
          <th></th>
          <th>商品名</th>
          <th>値段</th>
          <th>数量変更</th>
          <th>削除</th>
        </tr>
        <!--foreachのループで商品を表示する-->
        <?php foreach($data as $value){ ?>
        <tr>
          <td><img class="img" src="img/<?php print $value['img'];?>" /></td>
          <td><?php print $value['name'];?></td>
          <td><?php print $value['price'];?></td>
          <td>
            <form action="cart.php" method="post">
                <input class="text" type="text" name="amount" value="<?php print $value['amount']; ?>"  />
                <input type="hidden" name="push_button" value="change_stock">
                <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                <input class="change_stock" type="submit" name="change_stock" value="変更"/>
            </form>
          </td>
          <td>
             <form method="post">
                <input type="hidden" name="push_button" value="delete">
                <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                <input class="delete" type="submit" name="delete" value="削除"/>
             </form>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td>


          </td>
          <td class="sum">
              合計:
              <?php
              //forループで合計値を算出する
              //count($data)は INT 3である。
              $sum = 0;
              for($i=0;$i<count($data);$i++){
                  $sum = $sum + $data[$i]['price']*$data[$i]['amount'];
              }
              print $sum;
              ?>
              円
          </td>
        </tr>
      </table>
        
        <form class="buy" action="finish.php" method="post">
            <input type="hidden" name="user_id" value="">
            <input type="submit" value="購入する">
        </form>
     
     
      <?php if(count($err_msg) >0){
              foreach($err_msg as $value){?>
      <li>
        <?php print $value;?>
      </li>
      <?php }
          }
    }else{ ?>
    <p class="no_item">カートに商品がございません</p>
    <?php } ?>
    <p class="move">
      <a href="item_top.php">商品一覧ページへ戻る</a>
    </p>  
    </main>
    <footer>
      <section class="news">
        <p>NEWS</p>
        <!--hrefには外部リンク-->
        <a href="">MORE</a>
      </section>
      <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>
  </body>
</html>
