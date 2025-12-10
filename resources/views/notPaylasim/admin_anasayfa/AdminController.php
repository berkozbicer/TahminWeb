<?php


$dates = [];
$kayitlar = [];

@for($i = 6; $i >= 0; $i--){
    $date =  \Carbon\Carbon::today()->subDays($i);
    $dates[] = $date->format('d-m-Y');
    $kayitlar[] = User::whereDate('created_at', $date)->count();
}
