<?php if (!isset($_GET['proceed'])) {?>

    <h2>Warning: The new DB tables `Users` and `Requests` will be created and seeded from the data folder</h2>

    <a class="btn btn-danger" role="button" href="?controller=<?php echo $_GET['controller'];?>&action=<?php echo $_GET['action']?>&proceed=1">Proceed!</a>
<?php } elseif ($logs) {?>

    <p><?php echo $logs['users']?> users, <?php echo $logs['requests']?> requests sucessfully added! Let's go <a href="?controller=<?php echo $_GET['controller'];?>&action=index">View them!</a></p>

<?php } else {?>
    <h4>Something went wrong. Check the DB configuration please in connection.php</h4>
<?php } ?>
