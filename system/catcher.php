<?php
// Lockdown?
if(@get_table('Lockdown') AND !isset($_GET['override']))
  {
  if(strlen(get_table("Lockdown_Expiration")) > 0)
    $expiration = (strtotime(get_table('Lockdown_Expiration')) + get_table('dateoffset'));
  else
    $expiration = (time() + 86400);
  
  if(time() <= $expiration)
    die(error('Down for Maintenance', get_table('Lockdown') . "<br /><br />". get_table("Lockdown_Expiration")));
  }
?>