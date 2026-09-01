
<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

require_once '../connect.php';

$users = $db->query("
    SELECT *
    FROM users
    ORDER BY user_id DESC
");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>User Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-4">

<div class="d-flex justify-content-between mb-3">

<h2>
<i class="fa fa-users-cog"></i>
User Management
</h2>

<button
class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#addUser">

Add User

</button>

</div>

<div class="card shadow">

<div class="card-body">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Full Name</th>
<th>Username</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = $users->fetch(PDO::FETCH_ASSOC)): ?>

<tr>

<td><?php echo $row['user_id']; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['username']; ?></td>

<td><?php echo $row['role']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<a
href="user-edit.php?id=<?php echo $row['user_id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a
href="user-delete.php?id=<?php echo $row['user_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete User?')">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

<div class="modal fade" id="addUser">

<div class="modal-dialog">

<div class="modal-content">

<form action="user-save.php" method="POST">

<div class="modal-header">

<h5>Add User</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<label>Full Name</label>

<input
type="text"
name="full_name"
class="form-control"
required>

<br>

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
required>

<br>

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

<br>

<label>Role</label>

<select
name="role"
class="form-control">

<option value="Admin">Admin</option>
<option value="Cashier">Cashier</option>

</select>

<br>

<label>Status</label>

<select
name="status"
class="form-control">

<option value="Active">Active</option>
<option value="Inactive">Inactive</option>

</select>

</div>

<div class="modal-footer">

<button
type="submit"
class="btn btn-success">

Save User

</button>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

