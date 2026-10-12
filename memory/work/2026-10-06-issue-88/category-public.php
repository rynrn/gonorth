<?php
$map=[94=>'אטרקציות',95=>'טיולים',96=>'טיולים',97=>'לינה',427=>'טיולים',428=>'טיולים',464=>'מדריכים'];
$rows=[];
foreach($map as $id=>$expected){$rows[]=['id'=>$id,'url'=>get_permalink($id),'expected'=>$expected];}
echo wp_json_encode($rows,JSON_UNESCAPED_UNICODE);
