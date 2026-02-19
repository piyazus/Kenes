<?php
$a = $_GET['a'] ?? '';
$b = $_GET['b'] ?? '';
$result = '';

if ($a !== '' && $b !== '') {
    if ($a > $b) {
        $result = "A ($a) is larger";
    } elseif ($b > $a) {
        $result = "B ($b) is larger";
    } else {
        $result = "Both are equal ($a)";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Compare Numbers</title>
</head>
<body>
    <h1>Find Larger Number</h1>
    <form action="compare.php" method="GET">
        A: <input type="number" name="a" value="<?php echo $a; ?>"><br><br>
        B: <input type="number" name="b" value="<?php echo $b; ?>"><br><br>
        <button type="submit">Compare</button>
    </form>
    
    <?php if ($result): ?>
            <h2>Result: <?php echo $result; ?></h2>
    <?php endif; ?>
</body>
</html>