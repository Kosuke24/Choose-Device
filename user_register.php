<?php
// MySQL接続情報
$host     = 'localhost';
$username = 'codecamp28049';   // MySQLのユーザ名
$db_password = 'KHQESKVF';   // MySQLのパスワード
$dbname   = 'codecamp28049';   // MySQLのDB名
$charset  = 'utf8';   // データベースの文字コード
$new_name = '';
// MySQL用のDNS文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

//エラーメッセージ用配列
$err_msg = array();
//リザルト用変数
$result = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //名前の入力チェック
    if(isset($_POST['new_name']) === TRUE){
      //スペースチェック
      $new_name  = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['new_name']);
      $new_name = trim($new_name);
    }
    //名前の文字数チェック
    if(preg_match('/[0-9a-zA-Z]{6,}/',$new_name) !== 1){
      $err_msg[] = '名前は半角英数字6文字以上にしてください';
    }
    //名前の同一チェック DBのuser_nameに同じ名前がないか
    //パスワードの入力チェック
    if(isset($_POST['password']) === TRUE){
      //スペースチェック
      $password  = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['password']);
      $password = trim($password);
    }
    if(preg_match('/[0-9a-zA-Z]{6,}/',$password) !== 1){
      $err_msg[] = 'パスワードは半角英数字6文字以上にしてください';
    }
    if(empty($err_msg) === TRUE){
        //ユーザーIDとパスワードについて重複を調べる
        try{
          //DB接続
          $dbh = new PDO($dsn,$username,$db_password);
          //エラーモードとプリペアドステートメントの設定
          $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
          $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
          //postで送られてきた時にDBに追加
        //ユーザー情報をDBに追加する
          $now_date = date('Y:m:d H:i:s');
            //SQL文を作成
          $sql = 'INSERT INTO ec_user (user_name,password,create_datetime)
                  VALUES (?,?,?)';
          //SQL実行準備
          $stmt = $dbh->prepare($sql);
          //値をバインド
          $stmt->bindValue(1,$new_name,PDO::PARAM_STR);
          $stmt->bindValue(2,$password,PDO::PARAM_STR);
          $stmt->bindValue(3,$now_date,PDO::PARAM_STR);
          //SQL実行
          $stmt->execute();
          $result =  '登録完了';
        }catch(PDOException $e){
          $err_msg[] =  '入力内容に誤りがあります';
        }
    }
}
var_dump($err_msg);
 ?>
 <!DOCTYPE html>
 <html lnag="ja">
   <head>
     <meta charset="utf-8" />
     <title>ユーザー登録</title>
     <link rel="stylesheet" href="./css/user_register.css" />
     <link rel="stylesheet" href="./css/footer.css" />
   </head>
   <body>
     <header>
       <!--ロゴ画像-->
       <img src="./logo/logo.png" />
     </header>
     <main>
       <form method="post">
         <div class="margin-left">
           ユーザー名:<input type="text" name="new_name" placeholder="ユーザー名"/>
         </div>
         <div class="margin-left">
           パスワード:<input type="text" name="password"  placeholder="パスワード" />
         </div>
         <p>どのタイプのゲーミングデバイスをお探しですか?</p>
         <div class="radio">
           <input type="radio" name="select" value="" id="function"/ />
           <label for="function">機能重視</label>
           <input type="radio" name="select" value="" id="cost"/>
           <label for="cost">コスパ重視</label>
           <input type="radio" name="select" value="" id="design"/>
           <label for="design">デザイン重視</label>
         </div>
         <div class="clear">
           <input type="submit" value="ユーザー登録" />
           <ul>
             <?php if(count($err_msg) > 0){ ?>
             <?php foreach($err_msg as $value){ ?>
             <li><?php print $value ;?></li>
             <?php  } ?>
             <?php }else{ ?>
             <p><span><?php print $result; ?></span></p>
             <?php } ?>
           </ul>
         </div>
       </form>
       <p>
        <a href="login.php">ログイン画面へ戻る</a>
       </p>
     </main>
     <footer>
       <p>Copyright© KOSUKE All Rights Reserved.</p>
     </footer>
   </body>
 </html>
