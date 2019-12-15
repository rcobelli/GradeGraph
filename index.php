<?php

$ini = parse_ini_file("config.ini", true)["gg"];

try {
    $pdo = new PDO(
        'mysql:host=' . $ini['db_host'] . ';dbname=' . $ini['db_name'] . ';charset=utf8mb4',
        $ini['db_username'],
        $ini['db_password'],
        array(
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                            PDO::ATTR_PERSISTENT => false
                        )
    );
} catch (Exception $e) {
    die($e);
}


$handle = $pdo->prepare('SELECT DISTINCT `date` FROM Grades');
$handle->execute();
$result = $handle->fetchAll(\PDO::FETCH_ASSOC);
$dates = array();
foreach ($result as $d) {
    array_push($dates, $d['date']);
}

$handle = $pdo->prepare('SELECT DISTINCT `course` FROM Grades');
$handle->execute();
$result = $handle->fetchAll(\PDO::FETCH_ASSOC);
$courses = array();
$grades = array();
foreach ($result as $d) {
    array_push($courses, $d['course']);

    $handle = $pdo->prepare('SELECT grade FROM Grades WHERE course = ? ORDER BY date');
    $handle->bindValue(1, $d['course']);
    $handle->execute();
    $result2 = $handle->fetchAll(\PDO::FETCH_ASSOC);
    $grades[$d['course']] = array();
    foreach ($result2 as $g) {
        array_push($grades[$d['course']], $g['grade']);
    }
}

?>

<html>
<head>
    <style>
    body, html {
        background-color: #E5EEEF;
        color: #7A8C94;
    }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.js"></script>
</head>
<body>
    <div class="container pt-3">
        <h3>Grades This Semester</h3>
        <canvas id="myChart" width="125" height="75"></canvas>
        <script>
            var dynamicColors = function() {
                var r = Math.floor(Math.random() * 255);
                var g = Math.floor(Math.random() * 255);
                var b = Math.floor(Math.random() * 255);
                return "rgb(" + r + "," + g + "," + b + ")";
             };
            var ctx = document.getElementById('myChart');
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [
                        <?php
                            foreach ($courses as $c) {
                                echo "
                                {
                                    label: '" . $c . "',
                                    data: " . json_encode($grades[$c]) . ",
                                    fill: false,
                                    borderColor: dynamicColors()
                                },
                                ";
                            }
                        ?>
                    ]
                },
                options: {
                    scales: {
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Date'
                            }
                        }]
                    },
                }
            });
        </script>
    </div>
</body>
</html>
