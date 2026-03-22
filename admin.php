<?php

include "config.php";

$result = mysqli_query($conn,"SELECT * FROM channels");

?>

<!DOCTYPE html>

<html>

<head>

<title>Admin Panel</title>

<style>

body{font-family:Arial;background:#111;color:#fff}

table{width:100%;border-collapse:collapse}

td,th{padding:10px;border:1px solid #444}

a{color:yellow}

</style>

</head>

<body>

<h2>Admin Panel - Manage Channels</h2>

<a href="add_channel.php">+ Add Channel</a>

<table>

<tr>

<th>ID</th>

<th>Name</th>

<th>Stream URL</th>

<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['stream']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |

<a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>

</td>

</tr>

<?php } ?>

</table>

</body>

</html>