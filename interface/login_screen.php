<?php
$ignoreAuth=true;
include_once("./globals.php");
?>
<html>
<body>

<?php if($_SESSION['loginfailure'] == 1){
		$login_fail =1;
	  }
	  else{
	  	$login_fail =0;
	  }
		
?>		
<script LANGUAGE="JavaScript">
 top.location.href='<?php echo "../user_login.php?login_fail=$login_fail"; ?>';
</script>

<a href='<?php echo "$rootdir/login/new_login.php"; ?>'><?php xl('Follow manually','e'); ?></a>

<p>
<?php xl('OpenEMR requires Javascript to perform user authentication.', 'e'); ?>

</body>
</html>
