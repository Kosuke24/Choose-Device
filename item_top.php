<?php

// MySQL接続情報
$host     = 'localhost';
$db_username = 'codecamp28049';   // MySQLのユーザ名
$password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//イメージファイルのディレクトリ
$img_dir = './img/';
session_start();
//ログインしていたらユーザー名情報と、user_id情報を取得
if(isset($_SESSION['user_name']) === TRUE){
  $user_name = $_SESSION['user_name'];
  $user_id = $_SESSION['user_id'];
}else{
    header('Location:login.php');
    exit;
}

//DB接続
try{
  $dbh = new PDO($dsn,$db_username,$password);
  //エラーモードの設定とプリペアドステートメントをfalseにしとく
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

//以下商品表示処理------------------------------------------------------------------------------------------------------------------------------

  try{
      //SQL文を作成
      //id,name,pritce,img,status,stock
      $sql ='SELECT ec_item_master.item_id,ec_item_master.name,ec_item_master.price,
      ec_item_master.img,ec_item_master.status,ec_item_stock.stock
      FROM   ec_item_master INNER JOIN ec_item_stock
      ON     ec_item_master.item_id = ec_item_stock.item_id
      WHERE  ec_item_master.status = 1';
      //実行準備
      $stmt = $dbh->prepare($sql);
      //実行
      $stmt->execute();
      //配列として取得
      $row = $stmt->fetchAll();
      //htmlspecialcharsを適用
      $i = 0;
      foreach($row as $value){
        $data[$i]['item_id'] = htmlspecialchars($value['item_id'],ENT_QUOTES,'UTF-8');
        $data[$i]['item_name'] = htmlspecialchars($value['name'],ENT_QUOTES,'UTF-8');
        $data[$i]['img'] = htmlspecialchars($value['img'],ENT_QUOTES,'UTF-8');
        $data[$i]['price'] = htmlspecialchars($value['price'],ENT_QUOTES,'UTF-8');
        $data[$i]['stock'] = htmlspecialchars($value['stock'],ENT_QUOTES,'UTF-8');
        $i++;
      }
  }catch(PDOException $e){
    throw $e;
  }

//以下カートに入れるが押された時の処理-------------------------------------------------------------------------------------------------
  if(isset($_POST['item_id']) === TRUE){
    //現在日時を取得
    $now_date = date('Y:m:d H:i:s');
    // $item_id は商品のitem_id
    $item_id = $_POST['item_id'];
    try{
      //ec_cartのデータに user_id,item_id,amountの情報をinsert
    $sql = 'INSERT INTO ec_cart (user_id,item_id,amount,create_datetime)
            VALUES(?,?,1,?)
            ON DUPLICATE KEY UPDATE amount = amount +1,update_datetime = NOW()';
    //実行準備
    $stmt = $dbh->prepare($sql);
    //バインド
    $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
    $stmt->bindValue(2,$item_id,PDO::PARAM_INT);
    $stmt->bindValue(3,$now_date,PDO::PARAM_STR);

    //実行
    $stmt->execute();

    }catch(PDOException $e){
      throw $e;
    }
  }
}catch(PDOException $e){
  print $e->getMessage();
}

 ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title>商品一覧</title>
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
    <link rel="stylesheet" href="./css/item_top.css" />
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
      <!--商品-->
      <nav>
        <section>
          <p>商品の検索</p>
          <!-- デバイス 数字ごとに検索する-->
          <form method="post">
            <ul>
              <li>
                <input type="radio" value=1 id="mouse">
                <label for="mouse">マウス</label>
              </li>
              <li>
                <input type="radio" value=2 id="keyboard" />
                <label for="keyboard">キーボード</label>
              </li>
              <li>
                <input type="radio" value=3 id="mousepad" />
                <label for="mousepad">マウスパッド</label>
              </li>
              <li>
                <input type="radio" value=4 id="headset" />
                <label for="headset">ヘッドセット</label>
              </li>
            </ul>
            <!-- 嗜好性 数字ごとに検索する-->
            <ul>
              <li>
                <input type="radio" value=1/>機能性重視
              </li>
              <li>
                <input type="radio" value=2/>コスパ重視
              </li>
              <li>
                <input type="radio" value=3/>デザイン重視
              </li>
            </ul>
            <input type="submit" value="検索" />
          </form>
        </section>
      </nav>
      <article>
        <section>
          <p class="title">商品一覧</p>
          <!--商品を横に並べていく-->
          <div class="items">
            <?php foreach($data as $value){ ?>
              <div class="item">
                <div class="img_contents">                  
                  <img src="<?php print $img_dir . $value['img']; ?>"  />
                </div>
                <p>
                  <?php print $value['item_name']; ?>
                </p>
                <p>
                  <?php print $value['price']; ?>
                </p>
                <div class="review">
                  <a href="item_review.php?item_id=<?php print $value['item_id'];?>">レビューを見る</a>
                </div>
                <!-- ストックがあったらカートに追加という文字を、そうでなければ売り切れを表示する -->
                <?php if($value['stock'] > 0){ ?>
                  <form method="post">
                    <input type="submit" value="カートに追加" />
                    <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>" />
                  </form>
                <?php }else{ ?>
                  <p><span>売り切れ</span></p>
                <?php } ?>
              </div>
            <?php } ?>
          </div>
        </section>
      </article>
    </main>
    <aside>
      <!--ニュース jqueryで流れるようにする-->

    </aside>
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
