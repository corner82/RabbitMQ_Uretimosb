<?php

require_once 'workerSender.php';;

$inputFilters = array(
    'invoiceNo' => FILTER_SANITIZE_NUMBER_INT,
);
//$input = filter_input_array(INPUT_POST, $inputFilters);

$sender = new WorkerSender();
//$sender->execute($input['invoiceNo']);
$sender->execute($argv[0]);
