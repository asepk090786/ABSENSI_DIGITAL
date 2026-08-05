<?php
$db=new PDO('sqlite:'.__DIR__.'/../database/testing.sqlite');
$stmt=$db->query("PRAGMA table_info('users')");
$cols=$stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col){
    echo $col['cid'].":".$col['name']."\n";
}

$s=$db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='users'");
$row=$s->fetch(PDO::FETCH_ASSOC);
echo "\nSQL:\n".($row['sql']??'')."\n";
