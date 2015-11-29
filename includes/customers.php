<h3>Customers</h3>
<?php
$db = new DbClass();
$customers = $db->globalSelect('customers',array());
?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Firstname</th>
        <th>Lastname</th>
        <th>Email</th>
        <th>Mobile</th>
      </tr>
    </thead>
    <tbody>
	<?php if(!empty($customers)): 
		   for($i=0; $i<sizeof($customers);$i++) : 
	?>
      <tr>
        <td><?php echo $customers[$i]['id'];?> </td>
        <td><?php echo $customers[$i]['firstname'];?> </td>
        <td><?php echo $customers[$i]['lastname'];?> </td>
        <td><?php echo $customers[$i]['email'];?> </td>
        <td><?php echo $customers[$i]['mobile'];?> </td>
      </tr>
      <?php endfor;
	  else : 
	  ?>
      <tr>
       	<td colspan="5"><?php echo "No Customers Found !" ;?> </td>
      </tr>
	  <?php endif;
	  ?>
    </tbody>
  </table>
</div>