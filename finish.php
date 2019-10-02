<?php
// セッション開始
session_start();
//db接続情報
// MySQL接続情報
$host     = 'localhost';
$db_username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

// エラー配列用変数
$err_msg = array();
$data = array();
//ログイン情報を取得
if(isset($_SESSION['user_name']) === TRUE){
  $user_name = $_SESSION['user_name'];
  $user_id = $_SESSION['user_id'];
}else{
  header('Location:login.php');
  exit;
}
//DB接続
try{
  $dbh = new PDO($dsn,$db_username,$db_password);
  //エラーモードの設定とプリペアドステートメントをfalseにしとく
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  //購入するアイテムの表示データを取得
  //ステータスが公開と、在庫数があるかの確認をする

  $sql = 'SELECT ec_item_master.name,ec_item_master.price,ec_item_master.status,
                 ec_item_master.img,ec_cart.user_id,ec_cart.item_id,ec_cart.amount,ec_item_stock.stock
          FROM ec_cart INNER JOIN ec_item_master
          ON ec_cart.item_id = ec_item_master.item_id
          INNER JOIN ec_item_stock
          ON ec_cart.item_id = ec_item_stock.item_id
          WHERE user_id = ?';
  // 実行準備
  $stmt = $dbh->prepare($sql);
  // バインド
  $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
  // 実行
  $stmt->execute();
  // 配列として取得する
  $row = $stmt->fetchAll();
  // echo '<pre>';
  // var_dump($row);
  // htmlspecialcharsを適用
  $i = 0;
  foreach($row as $value){
    $data[$i]['name'] = htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8');
    $data[$i]['item_id'] = htmlspecialchars($value['item_id'],ENT_QUOTES,'UTF-8');
    $data[$i]['price'] = htmlspecialchars($value['price'],ENT_QUOTES,'UTF-8');
    $data[$i]['img'] = htmlspecialchars($value['img'],ENT_QUOTES,'UTF-8');
    $data[$i]['amount'] = htmlspecialchars($value['amount'],ENT_QUOTES,'UTF-8');
    $data[$i]['status'] = htmlspecialchars($value['status'],ENT_QUOTES,'UTF-8');
    $data[$i]['stock'] = htmlspecialchars($value['stock'],ENT_QUOTES,'UTF-8');
    $i++ ;
  }
//   echo '<pre>';
//   var_dump($data);
  //ストックが空じゃなかった時の記述
  foreach($data as $value){
      if($value['stock'] < $value['amount']){
          $err_msg[] = $value['name'] . 'の在庫がありません';
      }
      //ステータス確認
      if($value['status'] !== '1'){
          $err_msg[] = $value['name'] . 'は購入できません';
      }
  }
    if(empty($data) === TRUE){
        $err_msg[] = 'カートに商品がありません';
    }
    if(empty($err_msg) === TRUE){
      //statusが0じゃない確認
      //以下カートで商品を購入した際にec_cartテーブルからアイテム情報を削除する
      //トランザクションで処理する
       $dbh->beginTransaction();
       try{
         // クエリを作成
         $sql = 'DELETE FROM ec_cart WHERE user_id = ?';
         // 実行準備
         $stmt = $dbh->prepare($sql);
         // バインド
         $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
         // 実行
         $stmt->execute();
        //   以下商品テーブルから在庫数を減らす処理をする------------------------------------------------------------------
        // クエリを作成
        foreach($data as $value){
        $sql = 'UPDATE ec_item_stock SET stock = stock - ?,update_datetime = now()
                WHERE item_id = ?';
        $stmt = $dbh->prepare($sql);
        // バインド
         $stmt->bindValue(1,$value['amount'],PDO::PARAM_INT);        
         $stmt->bindValue(2,$value['item_id'],PDO::PARAM_INT);
         $stmt->execute();
        }
        $dbh->commit();
      }catch(PDOException $e){
          $dbh->rollback();
          throw $e;
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
    <title>購入完了ページ</title>
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
    <link rel="stylesheet" href="./css/finish.css" />
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
      <!-- 商品が購入できた時($data が取れた時は購入確定画面を表示し、
      そうでない時はアイテムトップ画面（意図せずこの購入画面に来た時など)へ遷移 -->
      <?php if(empty($err_msg) ==TRUE){ ?>
               
      <p class="result">注文が確定しました！ご利用ありがとうございました</p>
      <table>
        <tr class="head">
          <th></th>
          <th>商品名</th>
          <th>値段</th>
          <th>数量</th>
        </tr>
        <?php foreach($data as $value){ ?>
        <tr>
          <td><img class="img" src="./img/<?php print $value['img'];?>" /></td>
          <td><?php print $value['name'];?></td>
          <td><?php print $value['price'];?></td>
          <td><?php print $value['amount'];?></td>
        </tr>
      <?php } ?>
      </table>
      <div class="total">
        <p>
          合計:
          <?php
            $sum = 0;
            foreach($data as $value){
            $sum +=  $value['amount'] * $value['price'];
          }
            print $sum;?>
            円
        </p>
      </div>
      <p class="move">
        <a href="item_top.php">商品一覧ページへ戻る</a>
      </p>
    <?php }else{
        foreach($err_msg as $value){
            print '<p>' . $value . '</p>';
        }
    }?>
    </main>
    <footer>
      <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>
  </body>
</html>
