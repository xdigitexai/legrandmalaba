<?php include 'header.php'; ?>
<style>


.input {
  width: 5%;
  
  
}
	.input2 {
  width: 95%;
  
}




</style>

<center><h2>Bulk Categories Editor</h2></center>
<br>
   <div class="container-fluid">
      <table class="table" style="border:1px solid #ddd">
         <thead>
  
               <th class="input">ID</th>
               <th class="input2">Name</th>
            
         </thead>
      <tbody>
   

<form action="" method="post" enctype="multipart/form-data">
            
              <?php foreach($services as $service ):  ?>
          <tr>
                                            <td class="input">
											<div><input type="text" class="form-control" name="service[<?php echo $service["service_id"]; ?>]"  value="<?php echo $service["category_id"]; ?>" readonly></div>
			  </td>
                                            <td class="input2"><div><input type="text" class="form-control" name="name-<?php echo $service["category_id"]; ?>"  value="<?php echo $service["category_name"]; ?>"></div></td>
                                            

												
	   
                                            
                                     </tr>
<?php endforeach; ?>
                                   </div>
                              </tbody>
                           </table>
              
<br>
                            <center><button type="submit" class="btn btn-primary" >Save Changes</button></center>
      </form>
         </div>
      </div>
   </div>

</div>

<div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
 <div class="modal-dialog modal-dialog-center" role="document">
   <div class="modal-content">
     <div class="modal-body text-center">
       <h4>Are you sure you want to update the status?</h4>
       <div align="center">
         <a class="btn btn-primary" href="" id="confirmYes">Yes</a>
         <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
       </div>
     </div>
   </div>
 </div>
</div>




<?php include 'footer.php'; ?>
