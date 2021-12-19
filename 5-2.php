<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>mission5-2</title>
</head>
<body>
    <?php
    $dsn = 'データベース名';/*データベースの名前定義*/
    $user = 'ユーザー名';/*ユーザーの名前定義*/
    $password = 'パスワード';/*パスワード定義*/
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    /*↑PHPでデータベースに接続するためのもの。
    『array』以下はエラーを表示するためのもの。
    「pdo」は「PHP Database Object」の略*/
    
    #$sql = 'DROP TABLE tb51test'; /*デバッグ用。テーブルを削除*/
    #$stmt = $pdo->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS tb51test"
    . "("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name varchar(64)," /*varcharで空白を詰めないように。また名前はバイト数を食うようなのでとりあえず64バイト。あとで調整*/
    . "comment TEXT,"
    . "date datetime not null,"
    . "pass TEXT"
    . ");";
    $stmt = $pdo->query($sql); /*$sqlを実行*/
    
    $sql ='SHOW CREATE TABLE tb51test';/*デバッグ用。完成したら削除*/
    /*『SHOW CREATE TABLE tbtest』で「tbtest」テーブルを作るために必要な
    コマンドを出力する。出力されたコマンドをそのまま実行すると、「tbtest」
    テーブルと全く同じテーブルが出来上がる*/
    $result = $pdo -> query($sql); 
    foreach ($result as $row){
         echo $row[1];
    }
    $value_edit = "";
    $value_name = "";
    $value_comment = "";
    $value_pass = "";
            /*以下編集処理命令を受け取ったとき、フォームに初期表示*/
    if(empty($_POST["name"]) && empty($_POST["comment"]) && empty($_POST["erase"]) && !empty($_POST["edit"])) {
        if(!empty($_POST["pass"])) {
            $pass = $_POST["pass"]; /*パスワードが合っているか確認*/
            $id = $_POST["edit"];
            $sql = "SELECT pass FROM tb51test WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            foreach ($result as $row){
                $edit_pass = $row;
            }
            if($edit_pass == $pass) {
                $value_edit = $id;
                $sql = "SELECT name, comment, pass FROM tb51test WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchall();
                foreach ($result as $row) {
                    $value_name = $row[0];
                    $value_comment = $row[1];
                    $value_pass = $row[2]; /*フォームの変数に取得した値を代入*/
                }
            }
        } else {
            echo "パスワードが入力されていません";    
        }
    } elseif(!empty($_POST["erase"])) {
        echo "フォームが不正です";
    }
    ?>
    <form action="" method="post">
        名前　　　
        <input type="text" name="name" value="<?= $value_name ?>">
        <br>
        コメント　
        <input type="text" name="comment" value="<?= $value_comment ?>">
        <br>
        パスワード
        <input type="text" name="pass" value="<?= $value_pass ?>">
        <input type="submit" name="submit">
        <br>
        <br>
        削除　　　
        <input type="text" name="erase">
        <input type="submit" name="submit"><!--指定の番号の投稿を削除-->
        <br>
        編集　　　
        <input type="text" name="edit">
        <input type="submit" name="submit"><!--指定の番号の編集を開始-->
        <input type="hidden" name="edit_temp" value="<?= $value_edit ?>"><!--隠しフォーム。編集番号を指定-->
        <br>
    </form>
    <?php
    $sql = $pdo -> prepare("INSERT INTO tb51test (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
    $sql -> bindParam(':pass', $pass, PDO::PARAM_STR); 
    /*カラムと変数を紐付け*/
    
    /*以下投稿処理*/
    if(!empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["edit_temp"])) { /*入力フォームに入力があったかどうか判定*/ 
        if(!empty($_POST["pass"])) {
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $date = date("YmdHis");
            $pass = $_POST["pass"];
            $sql -> execute(); /*フォームの内容を投稿*/
        } else {
            echo "パスワードが入力されていません";
        }
    } elseif(empty($_POST["erase"]) && empty($_POST["edit"])) {
        echo "フォームが不正です";
    }
    
    /*以下削除処理*/
    if(empty($_POST["name"]) && empty($_POST["comment"]) && !empty($_POST["erase"]) && empty($_POST["edit"])) {
        if(!empty($_POST["pass"])) {
            $pass = $_POST["pass"]; /*パスワードが合っているか確認*/
            $id = $_POST["erase"];
            $sql = "SELECT pass FROM tb51test WHERE id = :id"; /*指定番号のパスワードを検索*/
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            foreach ($result as $row){
                $erase_pass = $row;
            }
            if($erase_pass == $pass) {
                $name = "削除";
                $comment = "削除";
                $sql = 'UPDATE tb51test SET name=:name,comment=:comment WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            echo "パスワードが入力されていません";    
        }
    } elseif(!empty($_POST["erase"])) {
        echo "フォームが不正です";
    }
    
    /*以下前回編集指示があった場合に処理*/
    if(!empty($_POST["name"]) && !empty($_POST["comment"]) && empty($_POST["erase"]) && !empty($_POST["edit_temp"])) {
        if(!empty($_POST["pass"])) {
            $pass = $_POST["pass"]; /*パスワードが合っているか確認*/
            $id = $_POST["edit_temp"];
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $sql = 'UPDATE tb51test SET name=:name,comment=:comment,pass=:pass WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            echo "パスワードが入力されていません";    
        }
    } elseif(!empty($_POST["edit_temp"])) {
        echo "フォームが不正です";
    }
    
    echo "<hr>";
    $sql = 'SELECT * FROM tb51test';
     /*『SELECT』文は指定した列、行を抽出する。
    『SELECT *』で全列を指定する。
    『FROM』は抽出するテーブルを表す。*/
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    /*『fetch』は実行したSQL文の結果を1行ごとに取得する。
    『fetchAll』ですべての行を配列として取得する。*/
    foreach ($results as $row){ /*デバッグ用。完成したら削除*/
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].',';
        echo $row['pass'].'<br>';
    echo "<hr>";
    }
    /*テーブルのデータをそれぞれ表示*/
    ?>


</body>
  </html>