<?phpgit push -u origin main

session_start();

session_destroy();

header("Location: login.php");

exit;

?>