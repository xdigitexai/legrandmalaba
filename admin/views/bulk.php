<?php include 'header.php'; ?>
<style>.input{width:5%}.input2{width:40%}.input3{width:7%}.input4,.input5{width:8%}.input6{width:30%}</style>

<center><h2>Bulk Service Editor</h2></center>
<br>
   <div class="container-fluid">
    <a href="javascript:void(0)" class="btn btn-primary" data-toggle="modal" data-target="#modalDiv" data-action="bulkGetCategories">Select Category</a><br><br>
       <form id="bulk-edit" action="" method="post" enctype="multipart/form-data">
      <table class="table" style="border:1px solid #ddd">
<thead>
  
<th class="input">ID</th>
<th class="input2">Name</th>
<th class="input3" >Min</th> 
<th class="input4" >Max</th>
<th class="input5" >Price</th>
<th class="input6" >Description</th>
            
         </thead>
      <tbody>

</tbody>
</table>
<br>
<center><button type="submit" class="btn btn-primary" >Save Changes</button></center>
</form>
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