<html>
<head>
<?php

	$match_arr=array("product", "file");
	$DB_TABLE_NAME="text_file";
	$DB_FULL_PATH="./db_file/cmd.db";

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
   
	function open_db($path) 
	{
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
		$cmd="SELECT DISTINCT ".$name." FROM text_file";
		return create_query_select($db, $name, $cmd, $default);
	}

	function create_db($path) 
	{
		$cmd="SELECT DISTINCT ".$name." FROM text_file";
		return create_query_select($db, $name, $cmd, $default);
	}

	function create_default_row($db) 
	{
		$cmd="INSERT INTO text_file 
		(file, owner, product, context) VALUES 
		('git', 'public', 'tips','git k');";

		execute_sql($db, $cmd);
	}

	function create_new_row($db) 
	{
		$cmd="INSERT INTO text_file 
		(file, owner, product, context) VALUES 
		('tmp', 'public', 'ready_to_use','git k');";

		execute_sql($db, $cmd);
	}

	function delete_row($db) 
	{
		$cmd="delete from 'text_file' where id=8;";

		execute_sql($db, $cmd);
	}
	
	//check DB file exist
	if(!file_exists($DB_FULL_PATH))
	{
		fopen($DB_FULL_PATH, "w");
	}

	$db=open_db($DB_FULL_PATH);

	// check table exist
	$query = "SELECT name FROM sqlite_master WHERE type='table' AND name='" . SQLite3::escapeString($DB_TABLE_NAME) . "'";
	$result = $db->query($query);

	// creta table
	if (!$result->fetchArray()) 
	{
		create_text_file($db);
	}

	$query = "SELECT product FROM ".$DB_TABLE_NAME." WHERE product='tips'";
	//echo $query;

	// create first row
	$result = $db->query($query);

	if (!$result->fetchArray()) 
	{
		create_default_row($db);
	}

	if(!isset($_POST['product']))
	{
		$query = "SELECT * FROM ".$DB_TABLE_NAME." WHERE product='tips'";

		$ret=$db->query($query);

		if($row = $ret->fetchArray())
		{
			$_POST['product']=$row['product'];
			$_POST['file']=$row['file'];
			$_POST['editor']=$row['context'];

			//echo $_POST['product'] ."<BR>";
			//echo $_POST['file'] ."<BR>";
			//echo $_POST['editor'] ."<BR>";
		}
	}
?>
<title>
<?PHP
if(isset($_POST['load']))
{
	echo $_POST['file'];
}
elseif (isset($_POST['save'])) 
{
	echo $_POST['file'];
}
elseif (isset($_POST['save_as'])) 
{
	echo $_POST['save_file'];
}
else 
{
	echo "editor";
}
?>
</title>
</head>
<body>
<form action="" method="post">
<?PHP
	$out_str=create_select($db, "product", $_POST['product']);
	echo $out_str[0];
	#echo "<br>";
?>
<button type="submit" name="load_product">load_product</button>
<?PHP
	$cmd="SELECT file FROM ".$DB_TABLE_NAME." where product=='".$_POST['product']."' ORDER BY file;";
	$save_as_err=0;

	if(isset($_POST['save_as']) && $_POST['save_file']!="")
	{
		$arr=array("product", "save_file");
		$insert_cmd="SELECT count(context) FROM text_file WHERE ";
		$insert_cmd.="product=='".$_POST["product"]."' AND ";
		$insert_cmd.="file=='".$_POST["save_file"]."' ; ";

		$ret=$db->query($insert_cmd);

		if($row = $ret->fetchArray())
		{
			if($row["count(context)"]==0)
			{
				$editor=str_replace("'", "''", $_POST['editor']);

				$insert_cmd="INSERT INTO ".$DB_TABLE_NAME." 
				(file, owner, product, context) VALUES 
				('".$_POST['save_file']."', 'public', '".$_POST['product']."', '".$editor."');";
				execute_sql($db, $insert_cmd);
			}
			else 
			{
				$save_as_err=1;
			}
		}

		$out_str=create_query_select($db, "file", $cmd, $_POST['save_file']);
	}
	else
	{
		$out_str=create_query_select($db, "file", $cmd, $_POST['file']);
	}
	echo $out_str[0];
?>
<button type="submit" name="load">load</button>
<button type="submit" name="save">save</button>
<?PHP
if(isset($_POST['save']))
{
	$arr=array("product", "file");

	$cmd="UPDATE text_file set context='".$_POST['editor']."' where ";

	foreach ($arr as &$condition) 
	{
		$cmd.=$condition."=='".$_POST[$condition]."' AND ";
	}

	$cmd.='1=1;';

	printn($cmd);
	execute_sql($db, $cmd);
}
?>
<!--
<button type="submit" name="delete">delete</button>
<?PHP
if(isset($_POST['delete']))
{
	$cmd="DELETE  FROM ".$DB_TABLE_NAME." WHERE ";
	$cmd.="product='".$_POST['product']."' and " ;
	$cmd.="file='".$_POST['file']."';" ;

	printn($cmd);
	execute_sql($db, $cmd);
}
?>
-->

<br><br>
<button type="submit" name="save_as">save as</button>
<textarea id="save_file" name="save_file" rows="1" cols="50">
<?PHP
if(isset($_POST['load']))
{
	echo $_POST['file'];
}
else if(isset($_POST['save_as']) && $_POST['save_file']!="")
{
	if($save_as_err!=0)
	{
		echo "file name must unique!";
	}
}
?>
</textarea>
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
	echo $_POST['editor'];
}

#$cmd="DELETE FROM ".$DB_TABLE_NAME." WHERE id=29;";
#$cmd="DELETE FROM ".$DB_TABLE_NAME." WHERE file='ifdef5';";
#execute_sql($db, $cmd);

$db->close();
?>
</textarea>
</form>
</body>