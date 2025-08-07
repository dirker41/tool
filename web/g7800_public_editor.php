<html>
<head>
<?php

    $match_arr=array("product", "file");
    $DB_TABLE_NAME="text_file";

    class SQLiteDB extends SQLite3
    {
        function __construct($path)
        {
            $this->open($path);
        }
    }

    function printn($str) 
    {
        /*
        echo "?>";
        echo "<script>";
        echo 'alert("'.$str.'")';
        echo "</script>";
        echo "<?php\n";
        */
        echo $str."<br>\n";
    } 
   
    function open_db() 
    {
        $path="../editor/db_file/cmd.db";
        $db = new SQLiteDB($path);
        if(!$db){
            echo $db->lastErrorMsg();
        }

        return $db;
    }

    function execute_sql($db, $cmd) 
    {
        $ret = $db->exec($cmd);
        if(!$ret){
            echo "ERROR:".$db->lastErrorMsg()."  ".$cmd."<br>";
        } 
    } 

    function create_text_file($db) 
    {
        execute_sql($db, "DROP table text_file");
        execute_sql($db, "CREATE TABLE text_file (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            file text NOT NULL,
            owner text NOT NULL,
            product text NOT NULL,
            context text NOT NULL
         );");
    } 

    function query_sql($db, $cmd) 
    {
        return $db->query($cmd);
    } 

    function create_query_select($db, $name, $cmd, $default) 
    {
        $out_str="$name:\n";
        $out_str.='<select name="'.$name.'">';
        $ret=$db->query($cmd);
        $selected="";
        $out_array= array();
        
        #printn($cmd);

        while($row = $ret->fetchArray())
        {
            array_push($out_array, $row[$name]);
        }

        if(count($out_array)>0)
        {
            $selected=$out_array[0];
        }
        
        for($i=0; $i<count($out_array); $i++ ) 
        {
            #printn($out_array[$i]);

            if($out_array[$i]==$default)
            {
                $out_str.="<option selected>".$out_array[$i]."</option>";
                $selected=$out_array[$i];
            }
            else 
            {
                $out_str.="<option>".$out_array[$i]."</option>";
            }
        }

        $out_str.="</select>\n";

        return array($out_str, $selected);
    }

    function create_select($db, $name, $default) 
    {
        $cmd="SELECT DISTINCT ".$name." FROM text_file where product=='g7800';";
        return create_query_select($db, $name, $cmd, $default);
    }

    function get_query($key) 
    {
        $queries = array();
        parse_str($_SERVER['QUERY_STRING'], $queries);
        return $queries[$key];
    } 

    $db=open_db();
?>
<title>

</title>
</head>
<body>
<form action="" method="post">
<?PHP
    $out_str=create_select($db, "product", $_POST['product']);
    echo $out_str[0];
    #echo "<br>";
?>

<?PHP
    $cmd="SELECT file FROM ".$DB_TABLE_NAME." where product=='".'g7800'."' ";
    $option1=" AND file LIKE 'CARD%' ";
    $option2=" ORDER BY file ";


    $cmd=$cmd.$option1.$option2;


    $out_str=create_query_select($db, "file", $cmd, $_POST['file']);

    echo $out_str[0];
?>
<button type="submit" name="load">load</button>

<br><br>

<?PHP
    if(isset($_POST['load']) or isset($_POST['trim']))
    {
        echo $_POST["file"];
    }
?>

<button type="submit" name="trim">trim</button>
<textarea id="editor" name="editor" rows="50" cols="220" >
<?PHP
if(isset($_POST['load']))
{
    $arr=array("product", "file");

    $cmd="SELECT context FROM text_file WHERE ";

    foreach ($arr as &$condition) 
    {
        $cmd.=$condition."=='".$_POST[$condition]."' AND ";
    }

    $cmd.="1==1;";
    #printn($cmd);
    $ret=$db->query($cmd);

    while($row = $ret->fetchArray())
    {
        echo $row["context"];
    }
}
else if(isset($_POST['save']) || isset($_POST['save_as']))
{
    echo $_POST['editor'];
}
else if(isset($_POST['trim']))
{
    $text=$_POST['editor'];

    while(strpos($text, "    ")!=false)
    {
        $text=str_replace("    ", "", $text);
    }

    echo $text;
}
else 
{
    $cmd="SELECT context FROM text_file WHERE ";
    $cmd.="product=='g7800' AND ";
    $cmd.="file=='".get_query("file")."' AND ";

    $cmd.="1==1;";
    #printn($cmd);
    $ret=$db->query($cmd);

    while($row = $ret->fetchArray())
    {
        echo $row["context"];
    }
}

#$cmd="DELETE FROM ".$DB_TABLE_NAME." WHERE id=29;";
#$cmd="DELETE FROM ".$DB_TABLE_NAME." WHERE file='ifdef5';";
#execute_sql($db, $cmd);

$db->close();
?>
</textarea>
</form>
</body>