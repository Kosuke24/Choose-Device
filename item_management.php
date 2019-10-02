<?php
// MySQL接続情報
$host     = 'localhost';
$db_username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード

// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//hidden分岐用変数
$insert_kind = '';
//エラーメッセ用配列
$err_msg = array();
//リザルトメッセージ用配列
$result_msg = array();
//保存用ディレクトリ
$img_dir = './img/';
//セッション　もし、ログインしていなかったらログインページへ遷移
session_start();
if(isset($_SESSION['user_name']) === TRUE){
  $user_name = $_SESSION['user_name'];
}else{
  header('Location:login.php');
  exit;
}
//ユーザーネームがaminでなければ商品一覧ページへリダイレクト
if($user_name !== 'admin'){
    header('Location:item_top.php');
    exit;
}

//商品追加ボタン、在庫アップデート、公開、非公開のいずれかのボタンが押された時
if(isset($_POST['insert_kind']) === TRUE){
    //変数に代入
  $insert_kind = $_POST['insert_kind'];;
}//商品追加ボタンを押す（$insert_kindが'add_item'）の時
  if($insert_kind === 'add_item'){
    //名前、値段、個数のスペース処理を行い、それぞれを変数に入れる。セキュリティ上　issetは確認しておく
    if(isset($_POST['new_name']) === TRUE){
      $new_name  = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['new_name']);
      $new_name = trim($new_name);
    }
    if(isset($_POST['new_price']) === TRUE){
      $new_price = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['new_price']);
      $new_price = trim($new_price);
    }
    if(isset($_POST['new_stock']) === TRUE){
      $new_stock = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['new_stock']);
      $new_stock = trim($new_stock);
    }
    //公開、非公開もisssetでセットされてるか確認し、値を変数に入れる
    if(isset($_POST['status']) === TRUE){
      $status = $_POST['status'];
    }
    //デバイスの種類がissetで確認し、値を変数に入れる
    if(isset($_POST['device_type']) === TRUE){
      $device_type = $_POST['device_type'];
    }
    //エラーメッセージの作成、名前が未入力の時
    if($new_name === ''){
      $err_msg[] = '名前が未入力です';
    }
    //値段が未入力のとき
    if($new_price === ''){
      $err_msg[] = '値段が未入力です';
    //値段が0以上の整数でないとき(正規表現)
  }elseif(preg_match('/\A\d+\z/',$new_price) !==1){
    $err_msg[] = '値段は0以上の整数にしてください';
    }
    //ストックが未入力の時
    if($new_stock ===''){
      $err_msg[] = '個数が未入力です';
    //ストックが0以上の整数出ない時
  }elseif(preg_match('/\A\d+\z/',$new_stock) !== 1){
    $err_msg[] = '個数は0以上の整数にしてください。';
  }
    //公開ステータスが未設定の時(値が0,1にマッチしない時)
    if(preg_match('/^[01]$/',$status) !== 1){
      $err_msg[] = '「ステータスが不正な値です」';
    }
    //デバイスの選択が不正かどうかチェックする
    if(preg_match('/^[1-4]$/',$status) !== 1){
      $err_msg[] = '[デバイスの種類が不正な値です]';
    }
    //ファイル系統の確認をする
    // HTTP POSTで送られているか確認する（一時ファイルで確認）
    if(is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE){
      //確認後、変数に入れとく
      $new_img = $_FILES['new_img']['name'];
      //拡張子チェックをしたいため、拡張子を取得しておく拡張子チェックをしたいため、拡張子を取得しておく
      $extension = pathinfo($new_img,PATHINFO_EXTENSION);
      //JPEGかPNGのみアップロード可能とする
      if($extension === 'jpeg' || $extension ==='png' || $extension ==='jpg'|| $extension ==='JPEG' || $extension ==='PNG' || $extension === 'JPG'){
        //保存していく。ユニークなファイル名を取得
        $new_img = md5(uniqid(mt_rand(),true)) . '.' . $extension;
        //同名ファイルがあるか確認
        if(is_file($img_dir . $new_img) !== TRUE){
          //指定ディレクトリに移動して保存
          if(move_uploaded_file($_FILES['new_img']['tmp_name'],$img_dir . $new_img) !== TRUE){
            $err_msg[] = 'ファイルアップロード失敗';
          }
          //同名ファイルがあることは通常ないので再アップロードさせる
        }else{
          $err_msg[] = 'ファイルアップロード失敗しました。再度お試しください';
        }
      //拡張子が違う時
      }else{
        $err_msg[] = '拡張子が異なります。拡張子は\'JPEG\'か\'PNG\'のみです';
      }
      //ファイルがHTTP POSTで来ていないということは、ファイルアップロードされていない
    }else{
      $err_msg[] = 'ファイルを選択してください。';
    }
  //在庫変更ボタンが押された時の処理
  }elseif($insert_kind === 'update'){
    //セキュリティ対策でisset確認
    if(isset($_POST['update_stock']) === TRUE){
      //スペース処理
      $update_stock = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['update_stock']);
      $update_stock = trim($update_stock);
    }
    if(isset($_POST['item_id']) === TRUE){
      $item_id = $_POST['item_id'];

    }
    //入力しているか確認
    if($update_stock === ''){
      $err_msg[] = '在庫を入力してください';
    //0以上か確認
  }elseif(preg_match('/\A\d+\z/',$update_stock) !==1){
      $err_msg[] = '在庫は0以上の整数にしてください';
    }
  // ステータスがチェンジを押した時の処理
}elseif($insert_kind === 'change_status'){
  //セキュリティ対策のためissset確認
  if(isset($_POST['change_status']) === TRUE){

    $change_status = $_POST['change_status'];

  }
  if(isset($_POST['item_id']) === TRUE){
    $item_id = $_POST['item_id'];

  }
  //changestatusが0,1じゃなかったらエラーを設定
  if(preg_match('/^[01]$/',$change_status) !== 1){
    $err_msg[] = 'ステータスが不正な値です';
  }
  //削除ボタンを押したときの処理
}elseif($insert_kind === 'delete'){
  //セキュリティ対策のためisset確認
  if(isset($_POST['item_id']) === TRUE){
    $item_id = $_POST['item_id'];
  }
}


//新規商品追加、在庫アップデート、ステータス変更の処理が終わったのでDB操作に映る
//DB接続
try{
  $dbh = new PDO($dsn,$db_username,$db_password);
  //エラーモードの設定とプリペアドステートメントをfalseにしとく
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
  //$err_msgがなく、POSTで送られてきた時にINSERT INTOや　UPDATEを行う
  if(count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST'){
    //現在日時を取得しておく
    $now_date = date('Y-m-d H:i:s');
    //新規商品が追加された時
    if($insert_kind === 'add_item'){
      //2つのテーブル ec_item_masterとec_item_stockを変更するため、整合性を保つようにトランザクションする
      $dbh->beginTransaction();

      try{
        //SQL文を作成
        $sql = 'INSERT INTO ec_item_master (name,price,img,status,create_datetime,device_type) VALUES(?,?,?,?,?,?)' ;
        // 実行準備
        $stmt = $dbh->prepare($sql);
        // 値をバインド 第３引数はデータ型
        $stmt->bindValue(1,$new_name,PDO::PARAM_STR);
        $stmt->bindValue(2,$new_price,PDO::PARAM_INT);
        $stmt->bindValue(3,$new_img,PDO::PARAM_STR);
        $stmt->bindValue(4,$status,PDO::PARAM_INT);
        $stmt->bindValue(5,$now_date,PDO::PARAM_STR);
        $stmt->bindValue(6,$device_type,PDO::PARAM_INT);
        //実行
        $stmt->execute();
        //INSERTしたIDを取得
        $item_id = $dbh->lastInsertId();

        //stockテーブルのSQLを作成
        $sql = 'INSERT INTO ec_item_stock (item_id,stock,create_datetime) VALUES (?,?,?)';
        // 実行準備
        $stmt = $dbh->prepare($sql);
        // 値をバインド
        $stmt->bindValue(1,$item_id,PDO::PARAM_STR);
        $stmt->bindValue(2,$new_stock,PDO::PARAM_INT);
        $stmt->bindValue(3,$now_date,PDO::PARAM_STR);
        // 実行
        $stmt->execute();
        //トランザクションをコミットする
        $dbh->commit();
        //追加成功したらメッセージを表示
        $result_msg[] = '追加成功';
      }catch(PDOException $e){
        //ロールバック処理
        $dbh->rollback();
        // 例外をスロー
        throw $e;
      }
      //在庫数を変更した時のDB処理をしていく
    }elseif($insert_kind === 'update'){
      try{
        //SQL文を作成
        $sql = 'UPDATE ec_item_stock SET stock=?,update_datetime=? WHERE item_id=?';
        //実行準備
        $stmt = $dbh->prepare($sql);
        // 値をバインド
        $stmt->bindValue(1,$update_stock,PDO::PARAM_INT);
        $stmt->bindValue(2,$now_date,PDO::PARAM_STR);
        $stmt->bindValue(3,$item_id,PDO::PARAM_INT);
        // 実行
        $stmt->execute();
        $result_msg[] = '在庫変更成功';
      }catch(PDOException $e){
        throw $e;
      }
      //ステータスが変更された時
    }elseif($insert_kind === 'change_status'){
      try{
        // SQL文を作成
        $sql = 'UPDATE ec_item_master SET status=?,update_datetime=? WHERE  item_id=?';
        // 実行準備
        $stmt = $dbh->prepare($sql);
        // 値をバインド
        $stmt->bindValue(1,$change_status,PDO::PARAM_INT);
        $stmt->bindValue(2,$now_date,PDO::PARAM_STR);
        $stmt->bindValue(3,$item_id,PDO::PARAM_INT);
        // 実行
        $stmt->execute();
        $result_msg[] = 'ステータス更新成功';
      }catch(PDOException $e){
        throw $e;
      }
      //削除ボタンを押した時
    }elseif($insert_kind === 'delete'){
      //2つのテーブル ec_item_masterとec_item_stockを変更するため、整合性を保つようにトランザクションする
      $dbh->beginTransaction();
      try{
        //商品データ(ec_item_master)の処理
        //SQLを作成
       $sql = 'DELETE FROM ec_item_master WHERE item_id=?';
       //実行準備
       $stmt = $dbh->prepare($sql);
       //値をバインド
       $stmt->bindValue(1,$item_id,PDO::PARAM_INT);
       //実行
       $stmt->execute();

       //商品在庫の処理(ec_item_stock)
       $sql = 'DELETE FROM ec_item_stock WHERE item_id=?';
       //実行準備
       $stmt = $dbh->prepare($sql);
       //値をバインド
       $stmt->bindValue(1,$item_id,PDO::PARAM_INT);
       //実行
       $stmt->execute();
       //コミットする
       $dbh->commit();
       $result_msg[] = '削除完了';
     }catch(PDOException $e){
       //ロールバック処理
       $dbh->rollback();
       throw $e;
     }
    }
  }
  try{
    //セレクト文作成
    $sql = 'SELECT ec_item_master.item_id,ec_item_master.name,ec_item_master.price,
                   ec_item_master.img,ec_item_master.status,ec_item_stock.stock
            FROM ec_item_master INNER JOIN ec_item_stock
            ON ec_item_master.item_id = ec_item_stock.item_id';
    //準備
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    //それぞれ配列に入れてhtmlspecialcharsを適用
    //無駄な数値なくなってきれいな連想配列になった。
    $i = 0;

    foreach ($rows as $row) {
      $data[$i]['item_id']   = htmlspecialchars($row['item_id'],   ENT_QUOTES, 'UTF-8');
      $data[$i]['name'] = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
      $data[$i]['price']      = htmlspecialchars($row['price'],      ENT_QUOTES, 'UTF-8');
      $data[$i]['img']        = htmlspecialchars($row['img'],        ENT_QUOTES, 'UTF-8');
      $data[$i]['status']     = htmlspecialchars($row['status'],     ENT_QUOTES, 'UTF-8');
      $data[$i]['stock']      = htmlspecialchars($row['stock'],      ENT_QUOTES, 'UTF-8');
      $i++;
    }

  }catch(PDOException $e){
    throw $e;
  }
}catch(PDOException $e){
  $err_msg[] = '予期せぬエラーが発生しました。原因：' . $e->getMessage();
}
 ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <title>管理画面</title>
    <link rel="stylesheet" href="./css/item_management.css" />
    <link rel="stylesheet" href="./css/header.css" />
    <link rel="stylesheet" href="./css/footer.css" />
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
      <!-- 以下商品追加フォーム ----------------------------------------------------------------------------------------------------->
      <div class="form">
        <h2>新規商品追加</h2>
        <form method="post" enctype="multipart/form-data">
          <div><lebel>名前:<input type="text" name="new_name" /></lebel></div>
          <div><label>値段:<input type="text" name="new_price" /></label></div>
          <div><label>個数:<input type="text" name="new_stock" /></label></div>
          <div><input type="file" name="new_img" /></div>
          <select name="status">
            <option value="0">
              非公開
            </option>
            <option value="1">
              公開
            </option>
          </select>
          <select name="device_type">
            <option value="1">
              マウス
            </option>
            <option value="2">
              マウスパッド
            </option>
            <option value="3">
              キーボード
            </option>
            <option value="4">
              ヘッドセット
            </option>
          </select>
          <div><input type="submit" value="商品追加"></div>
          <input type="hidden" name="insert_kind" value="add_item">


<?php
    //$result_msgを表示する
    if(count($result_msg) > 0){
      foreach($result_msg as $value){ ?>
        <p><?php print $value;?></p>
      <?php }
    }
    //$err_msgを表示する
    if(count($err_msg) > 0){
      foreach($err_msg as $value){ ?>
        <p><?php print $value;?></p>
      <?php }
    }
?>
      </form>
      </div>
      <!-- 以下商品管理一覧画面 ------------------------------------------------------------------------------------------------------------>
      <div class="item">
        <h2>商品情報変更</h2>
        <table>
          <tr>
            <th>商品画像</th>
            <th>商品名</th>
            <th>価格</th>
            <th>在庫数</th>
            <th>ステータス</th>
            <th>削除</th>
          </tr>
          <?php if(isset($data)){ ?>
            <?php foreach($data as $value){ ?>
            <tr>
              <!--画像-->
              <td><img src="<?php print $img_dir . $value['img'];?>"</td>
              <!--商品名-->
              <td><?php print $value['name'];?></td>
              <!--価格-->
              <td><?php print $value['price'];?></td>
              <td>
                <!--在庫数-->
                <form method="post">
                  <input class="input_text" type="text" name="update_stock" size=10 value="<?php print $value['stock'];?>"/>個
                  <input type="submit" value="変更" />
                  <input type="hidden" name="insert_kind" value="update" />
                  <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>" />
                </form>
              </td>
              <!--ステータス-->
              <td>
                <form method="post">
                  <!--valueはphpで分岐,$statusが0の時は 非公開→公開, $statusが1の時は公開→非公開-->
                  <?php if($value['status'] === '0'){ ?>
                  <input type="submit" value="非公開→公開">
                  <!--非公開→公開はボタンを押すと1を送る-->
                  <input type="hidden" name="change_status" value="1"/>
                <?php }else{ ?>
                  <!--statusが0の時、公開→非公開はボタンを押すと0を送る-->
                  <input type="submit" value="公開→非公開" />
                  <input type="hidden" name="change_status" value="0" />
                <?php } ?>
                  <!--valueは配列展開-->
                  <input type="hidden" name="insert_kind" value="change_status" />
                  <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>" />
                </form>
                <!--削除ボタン-->
                <td>
                  <form method="post">
                    <input type="submit" value="削除" />
                    <input type="hidden" name="insert_kind" value="delete" />
                    <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                  </form>
                </td>
              </td>
            </tr>
        <?php } ?>
      <?php } ?>
        </table>
      </div>
    </main>
    <footer>
        <p>Copyright© KOSUKE All Rights Reserved.</p>
    </footer>
  </body>
</html>
