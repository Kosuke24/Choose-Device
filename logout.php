<?php
//セッション開始

session_start();

// セッション名を取得
$session_name = session_name();

//セッション変数を削除
$_SESSION = array();

//ユーザーのクッキー情報に登録されているセッションIDを削除
if(isset($_COOKIE[$session_name])){
  //セッションに関する設定情報を取得し、変数に代入する
  $params = session_get_cookie_params();

  //sesssionに利用しているクッキーを削除
    setcookie($session_name, '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
//セッションIDを無効化する
session_destroy();
//ログアウト処理が終わったらログインページへ遷移する
header('Location:login.php');
exit;

 ?>