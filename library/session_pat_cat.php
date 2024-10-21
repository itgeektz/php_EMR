<?php
    require_once('../interface/globals.php');

    $res = sqlQuery("SELECT lo.title FROM list_options lo LEFT JOIN patient_data pd ON pd.patient_category = lo.option_id where lo.list_id = 'Patient_Category' AND pd.pid= '$_SESSION[pid]'");

    $_SESSION['pat_cat'] = $res['title'];

    echo $_SESSION['pat_cat'];
?>
